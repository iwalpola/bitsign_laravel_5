<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Contract;
use App\User;
use UCrypt;
use Cache;
use XmlDSig;

class SignatureController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($contract_id)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($contract_id)
    {
        //get contract data
        $contract_data = $this->getContractData($contract_id);
        //takes doc_id and appends to data array, then redirects to signature page

        $data = array(
        'contract_data'  => $contract_data,
        'subheading1'   => 'Contracts',
        'subheading2' => 'Sign Contract',
        'subheading3' => 'Sign'
        );

        //returns an signing page
        return view('signature.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $contract = Contract::find($request->contract_id);
        $user = Auth::user();
        $signeevalidity = $this->isInContract($user->id,$contract);
        if (is_null($user->signkeyname_enc)) {
            return response()->json(['success' => 3, 'message' => 'We don\'t have a valid key file in your name.']);
        }
        if ($signeevalidity==='complete') {
            return response()->json(['success' => 2, 'message' => 'You have already signed this Contract']);
        }
        if ($signeevalidity===true) {
            $this->signIt($user, $contract);
            return response()->json(['success' => 1, 'message' => 'Successfully Signed']);
        }
        else {
            return response()->json(['success' => 0, 'message' => 'You are not in this Contract']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Sign the contract.
     *
     * @param  int  $contract_id
     * @return null
     */
    protected function signIt(User $user, Contract $contract)
    {
        //set XMLDigitalSignature options
        XmlDSig::setCryptoAlgorithm(1);
        XmlDSig::setDigestMethod('sha512');
        XmlDSig::forceStandalone();
        //load the private key
        $privkeypass = Cache::get($user->id);
        $privname = Cache::get($user->id.'priv');
        $pubname = $user->pubkey;
        try
        {
            XmlDSig::loadPrivateKey(storage_path('keys/'.$privname.'.pem'), $privkeypass);
            XmlDSig::loadPublicKey(storage_path('keys/'.$pubname.'.pem'));
            XmlDSig::loadPublicXmlKey(storage_path('keys/'.$pubname.'.xml'));
        }
        catch (\UnexpectedValueException $e)
        {
            print_r($e);
            exit(1);
        }
        //get signature
        $sigrecord = $contract->signatures->where('user_id',$user->id)->first();
        //load priate key to memory
        $privkeymem =  openssl_pkey_get_private(
                file_get_contents(storage_path('keys/').$privname.'.pem'),
                $privkeypass
            );
        //get contract key
        $pubenc_contractkey = $sigrecord->contractkey_enc;
        openssl_private_decrypt(
            base64_decode($pubenc_contractkey),
            $dcrypted_contractkey,
            $privkeymem
        );
        //check document existence
        $filepath = storage_path('contracts/').$contract->id.'/contract.xml';
        if (file_exists($filepath)) {
            //decrypt the contract XML
            $encrypteddata = file_get_contents($filepath);
            UCrypt::setKey($dcrypted_contractkey);
            $decrypteddata = UCrypt::decrypt($encrypteddata);
            $econtract = new \DOMDocument;
            $econtract->loadXML($decrypteddata);
            $signdata = $econtract->getElementsByTagName('contract')[0];
            try{
                XmlDSig::addObject($signdata, 'contractcontent', true);
                XmlDSig::sign();
                XmlDSig::verify();
            }
            catch (\UnexpectedValueException $e){
                print_r($e);
                exit(1);
            }
            //set signature filepath, and write to file
            $sigfpath = storage_path('contracts/'.$contract->id.'/dsig_').$user->f_name.'_'.$user->l_name.'.xml';
            file_put_contents($sigfpath, XmlDSig::getSignedDocument());
            //hash that thang
            $sha384 = hash('sha384', file_get_contents($sigfpath));
            $sigrecord->hash = $sha384;
            $sigrecord->status = true;
            $sigrecord->save();
        }
    }

    /**
     * Check if this user is in this contract's signature records.
     *
     * @param  int  $user_id
     * @param  int  $contract_id
     * @return boolean or string 'complete'
     */
    protected function isInContract($user_id, Contract $contract)
    {
        if ($signee = $contract->signatures->where('user_id',$user_id)->first()) {
            if ($signee->status==false) {
                return true;
            }
            return 'complete';
        }
        return false;
    }

    /**
     * Fetch from database or filesystem.
     *
     * @return string $contract_data
     */
    protected function getContractData($contract_id)
    {
        $auth_user_id = Auth::user()->id;
        $contract = Contract::find($contract_id);
        //set filepath
        $filepath = storage_path('contracts\\').$contract_id.'.xml';
        //get the contract decryption key
        if ($contract->user_id != $auth_user_id) {
            $pkeyname = Cache::get($auth_user_id.'priv').'.pem';
            $pubenc_contractkey = $contract->signatures->where('user_id',$auth_user_id)->first()->contractkey_enc;
            openssl_private_decrypt(
                base64_decode($pubenc_contractkey),
                $dcrypted_contractkey,
                openssl_pkey_get_private(
                    file_get_contents(storage_path('keys').'/'.$pkeyname),
                    Cache::get($auth_user_id)
                )
            );
        }
        else {
            $contract->setSecret(Cache::get($auth_user_id));
            $dcrypted_contractkey = $contract->key;
        }
        //set the contract data key
        $contract->setSecret($dcrypted_contractkey);
        //get data and fill in array
        $data['id'] = $contract->id;
        $data['title'] = $contract->title;
        $data['body'] = $contract->content;
        $filerecords = $contract->filerecords;
        $data['contracttype'] = $contract->contracttype->name;
        $data['key'] = $dcrypted_contractkey;
        $signatures = $contract->signatures;
        //arrange the signing individuals into parties and fill in $data
        foreach ($signatures as $signature) {
            $signee = $signature->user;
            $saddress = $signee->address;
            $addressstring = $saddress->line_1.', '.$saddress->line_2.', '.$saddress->city.', '.$saddress->state.' '.$saddress->postalcode.', '.$saddress->country;
            $data['parties'][$signature->term][] = [$signee->f_name.' '.$signee->l_name, $addressstring];
        }
        //arrange the filerecord necessary into data
        foreach ($filerecords as $key=>$filerecord) {
            $data['filerecords'][$key] = [
                'hash'=>$filerecord->hash,
                'filename'=>$filerecord->filename,
                'type'=>$filerecord->type
            ];
            # code...
        }
        //build doc if legalXML not built
        if (strlen($contract->hash)==0) {
            $buildreturn = $this->assembleDoc($data, $filepath);
            $contract->hash = $buildreturn[0];
            $data['date'] = $buildreturn[1];
            $contract->save();
        }
        else{
            //get date
            $contractdoc = new \DOMDocument;
            $contractdoc->load($filepath);
            $data['date'] = $contractdoc->getElementsByTagName('date')[0]->nodeValue;
        }
        //if document exists, confirm hash, else rebuild
        if (file_exists($filepath)) {
            //set key
            UCrypt::setKey($dcrypted_contractkey);
            //get encrypted data
            $encrypteddata = file_get_contents($filepath);
            //decrypt
            $filedata = UCrypt::decrypt($encrypteddata);
            //check hash and return
            if (base64_encode(hash('sha384',$filedata,true))==$contract->hash) {
                $data['hash'] = $contract->hash; 
            }
            else{
                abort(422);
            }
        }
        return $data;
    }

    /**
     * Assemble the XML from everything.
     *
     * @return string $contracthash
     */
    protected function assembleDoc($data, $filepath)
    {
        //load xml from template
        $doc = new \DOMDocument;
        $doc->load(storage_path('xmltemplates/legalxml/').$data['contracttype'].'.xml');
        //set title
        $doc_title = $doc->getElementById('title');
        $doc_title->nodeValue = $data['title'];
        //set date
        $doc_date = $doc->getElementsByTagName('date')[0];
        $contractdate = date(DATE_RFC2822);
        $doc_date->nodeValue = $contractdate;
        //set parties
        $partiesnode = $doc->getElementsByTagName('parties')[0];
        foreach ($data['parties'] as $party => $personrecords) {
            $partynode = $partiesnode->appendChild($doc->createElement("party"));
            $firstpass = true;
            foreach ($personrecords as $personrecord) {
                if ($firstpass) {
                    $firstpass = false;
                }
                else{
                    $partynode->appendChild($doc->createTextNode("and"));
                }
                $prnode = $partynode->appendChild($doc->createElement("person-record"));
                $prnode->appendChild($doc->createElement("name",$personrecord[0]));
                $prnode->appendChild($doc->createTextNode("of"));
                $prnode->appendChild($doc->createElement("address",$personrecord[1]));
            }
            if (count($personrecords) == 1) {
                $partynode->appendChild($doc->createTextNode(', hereafter referred to as "'));
            }
            else{
                $partynode->appendChild($doc->createTextNode(', hereafter collectively referred to as "'));
            }
            $partynode->appendChild($doc->createElement("term", $party));
            $partynode->appendChild($doc->createTextNode('"'));
        }
        //write content
        $newnode = $doc->createDocumentFragment();
        $newnode->appendXML(str_replace('&nbsp;', '&#xA0;', $data['body']));
        $doc->getElementById('contract-content')->appendChild($newnode);
        //write filerecords
        $attachmentsnode = $doc->getElementsByTagName('attachments')[0];
        foreach ($data['filerecords'] as $filerecord) {
            $attachmentnode = $attachmentsnode->appendChild($doc->createElement('attachment'));
            $objectnode = $attachmentnode->appendChild($doc->createElement('object'));
            $objectnode->setAttribute("type", $filerecord['type']);
            $datanode = $objectnode->appendChild($doc->createElement('data'));
            $datanode->setAttribute("src", $filerecord['filename']);
            $hashnode = $objectnode->appendChild($doc->createElement('hash', $filerecord['hash']));
            $hashnode->setAttribute("algorithm", "sha384");
        }
        //save the file
        $doc->save($filepath);
        //get the hash of it
        $contracthash = base64_encode(hash_file('sha384', $filepath, true));
        //encrypt the document
        $unencryptedfile = file_get_contents($filepath);
        UCrypt::setKey($data['key']);
        file_put_contents($filepath, UCrypt::encrypt($unencryptedfile));
        //return the contract data
        return array($contracthash,$contractdate);
    }
}
