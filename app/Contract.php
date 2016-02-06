<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use Encryption;
    /**
	 * The parts of the users table that are fillable.
	 *
	 * @var array
	 */

	protected $fillable = ['title','content', 'type'];

    /**
     * The attributes that are encrypted.
     *
     * @var array
     */
    protected $encrypted = ['title', 'content', 'key'];

	/**
     * Get the user that created the contract.
     */

	public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get all of the filerecords attached to this contract.
     */

    public function filerecords()
    {
        return $this->hasMany(FileRecord::class);
    }

    /**
     * Get all of the signatures attached to this contract.
     */

    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }

    /**
     * Get all of the pending signature requests attached to this contract.
     */

    public function pendingsigrequests()
    {
        return $this->hasMany(PendingSigrequest::class);
    }

    /**
     * Get this contract's type.
     */

    public function contracttype()
    {
        return $this->belongsTo(ContractType::class);
    }
}
