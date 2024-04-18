<?php

namespace App\Http\Controllers;

use App\Services\ViaCepService;
use Illuminate\Support\Facades\Validator;

class AddressesController extends Controller
{
    protected $viaCepService;

    public function __construct(ViaCepService $viaCepService)
    {
        $this->viaCepService = $viaCepService;
    }

    /**
     * Display a listing of the searched resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search($cep)
    {

        $cep = $this->formatCep($cep);

        // Valida o campo CEP
        $validator = Validator::make(
            ['cep' => $cep],
            ['cep' => 'required|regex:/^\d{5}-\d{3}$/'],
            ['cep.required' => 'O campo CEP é obrigatório!', 'cep.regex' => 'Formato de CEP inválido. Use conforme o exemplo: 83215-360']
        );

        // Caso a validação não passe, retorna mensagem de erro
        if ($validator->fails()) {
            return response(['status' => 'error', 'message' => 'Houve um erro ao validar os dados!', 'errors' => $validator->errors()], 400);
        }

        // Se não encontrado localmente, tente obter o endereço externo
        $address = $this->viaCepService->getAddress($cep);

        if ($address === false || isset($address->erro)) {
            return response(['status' => 'error', 'message' => 'Endereço não encontrado!'], 404);
        }

        return response(['status' => 'success', 'data' => [$address]], 200);
    }

    // Formata o CEP para fazer a busca
    private function formatCep($cep)
{
    // Remove qualquer caracter que não seja número do CEP
    $cep = preg_replace('/\D/', '', $cep);

    // Adiciona o hífen se o CEP tiver 8 dígitos
    if (strlen($cep) == 8) {
        $cep = substr($cep, 0, 5) . '-' . substr($cep, 5);
    }

    return $cep;
}
}
