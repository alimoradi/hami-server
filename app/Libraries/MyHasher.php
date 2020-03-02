<?php
namespace App\Libraries;
use Illuminate\Contracts\Hashing\Hasher;

class MyHasher implements Hasher {

    public function make($value, array $options = array()) {
        //$value = env('SALT', '').$value;
        //return md5($value);
        return $value;
    }

    public function check($value, $hashedValue, array $options = array()) {
        //return $this->make($value) === $hashedValue;
        return $value === $hashedValue;
    }

    public function needsRehash($hashedValue, array $options = array()) {
        return false;
    }

}
