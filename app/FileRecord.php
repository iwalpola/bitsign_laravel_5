<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileRecord extends Model
{
    /**
	 * The parts of the users table that are fillable.
	 *
	 * @var array
	 */

	protected $fillable = ['hash','filename','contract_id', 'type', 'encrypted'];

	/**
     * Get the contract that this filerecord belongs to.
     */

	public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
