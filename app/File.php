<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{

	use SoftDeletes;

	/**
	 * Fields that can be approved by admin
	 */
	const APPROVAL_PROPERTIES = [
		'title',
		'overview_short',
		'overview',
	];

	protected $fillable = [
		'title',
		'overview_short',
		'overview',
		'price',
		'live',
		'approved',
		'finished',
	];

	/**
	 * Uniquely generate a "identifier" ID each time we are creating a files.
	 * Override parent boot method on Model.
	 */
	protected static function boot() {
		parent::boot();

		// When we are creating a file, we get an instance of this file.
		static::creating(function($file) {
			// Set a unique ID as the identifier
			$file->identifier = uniqid(true);
		});
	}

	/**
	 * When we pass a file in the URL, we want the "identifier" column in be the URL NOT the "id" of it.
	 * @return string
	 */
	public function getRouteKeyName() {
		return 'identifier';
	}


	/**Return all files where "finished" = true in database
	 * @param Builder $builder
	 *
	 * @return mixed
	 */
	public function scopeFinished(Builder $builder) {
		return $builder->where('finished', true);
	}


	/**
	 * Check if the file is free. (Equal to 0 in database.)
	 * @return bool
	 */
	public function isFree() {
		return $this->price === 0;
	}


	/**
	 * Check if the new data passed in matches the old file data.
	 * If not, then return this method as true, whci will mean this file needs approval from admin.
	 * @param array $approvalProperties
	 *
	 * @return bool
	 */
	public function needsApproval(array $approvalProperties) {
		// Check if the data being passed in is equal to the old data, if not,
		// return true, (it needs approval)
		if($this->currentPropertiesDifferToGiven($approvalProperties)) {
			return true;
		}

		// Else, return false
		return false;
	}


	/**
	 * Create an approval in approvals table referencing 'approvals' relationship.
	 * @param array $approvalProperties
	 */
	public function createApproval(array $approvalProperties) {
		$this->approvals()->create($approvalProperties);
	}

	/**
	 *  Do the current properties of this model differ to the data we are given into this method.
	 * @param array $properties
	 *
	 * @return bool
	 */
	protected function currentPropertiesDifferToGiven(array $properties) {
		return array_only($this->toArray(), self::APPROVAL_PROPERTIES) != $properties;
	}

	/**
	 * A file belongs to a user.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
    public function user() {
    	return $this->belongsTo(User::class);
    }


	/**
	 * A file can have many approvals.
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
    public function approvals() {
    	return $this->hasMany(FileApproval::class);
    }
}