<?php

namespace hollisho\repository;

use Illuminate\Database\Eloquent\Model;

class UnixTimestampModel extends Model
{
    protected $dateFormat = 'U';

    public function getCreatedAtAttribute($value) {
        return $this->fromDateTime($value);
    }

    public function getUpdatedAtAttribute($value) {
        return $this->fromDateTime($value);
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
}