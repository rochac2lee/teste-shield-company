<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'fantasy_name',
        'legal_name',
        'cnpj',
        'email',
        'phone',
        'cep',
        'street',
        'complement',
        'district',
        'city',
        'uf'
    ];

    public static $rules = [
        'fantasy_name' => 'nullable|string|max:255',
        'legal_name' => 'nullable|string|max:255',
        'cnpj' => 'required|string|max:18|unique:companies,cnpj',
        'email' => 'required|email|unique:companies,email',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:100',
        'state' => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
    ];

    public static $messages = [
        'cnpj.unique' => 'Uma empresa já está cadastrada com esse CNPJ.',
        'email.unique' => 'Este endereço de e-mail já está em uso.',
    ];

    public static function getRules($id = null)
    {
        return array_map(function ($rule) use ($id) {
            if (is_string($rule)) {
                return str_replace(':id', $id ?? 'NULL', $rule);
            }
            return $rule;
        }, self::$rules);
    }

    protected $primaryKey = "id";
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted()
    {
        static::creating(fn (Company $company) => $company->id = (string) Str::uuid());
    }
}
