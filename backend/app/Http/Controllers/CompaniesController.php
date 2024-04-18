<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CompaniesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $companies = Company::get();
            return response(['status' => 'success', 'data' => $companies, 'total' => sizeof($companies)], 200);
        } catch (QueryException $e) {
            Log::error('Erro ao listar empresas: ' . $e->getMessage(), ['request' => request()->all()]);
            return response(['status' => 'error', 'message' => 'Ocorreu um erro ao listar as empresas. Por favor, tente novamente mais tarde.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Valida se os campos obrigatórios foram informados
        try {
            request()->validate(Company::$rules, Company::$messages);
        } catch (ValidationException $e) {
            Log::error('Erro ao cadastrar empresa: ' . $e->getMessage(), ['request' => request()->all()]);
            return response(['status' => 'error', 'message' => 'Houve um erro ao validar os dados!', 'errors' => $e->errors()], 422);
        }

        $company = Company::create(request()->all());

        return response(['status' => 'success', 'message' => 'Empresa cadastrada com sucesso!'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        try {
            return response(['status' => 'success', 'data' => $company], 200);
        } catch (Exception $e) {
            // Log do erro
            Log::error('Erro ao buscar empresa: ' . $e->getMessage());
            return response(['status' => 'error', 'message' => 'Ocorreu um erro ao buscar a empresa. Por favor, tente novamente mais tarde.'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        try {

            $company = Company::find($id);

            // Verifica se a empresa existe
            if (!$company) {
                return response(['status' => 'error', 'message' => 'Empresa não encontrada'], 404);
            }

            // Valida se já tem uma empresa com o CNPJ informado
            $hasCompanyWithCNPJ = Company::where('cnpj', request()->cnpj)->exists();
            if (isset(request()->cnpj) && $hasCompanyWithCNPJ && $company->cnpj != request()->cnpj) {
                return response(['status' => 'error', 'message' => 'Já existem uma empresa cadastrada com esse CNPJ'], 409);
            }

            $hasCompanyWithEmail = Company::where('email', request()->email)->exists();
            if (isset(request()->email) && $hasCompanyWithEmail && $company->email != request()->email) {
                return response(['status' => 'error', 'message' => 'Já existem uma empresa cadastrada com esse email'], 409);
            }

            // Atualiza os dados da empresa
            $company->update(request()->all());

            return response(['status' => 'success', 'message' => 'Empresa atualizada com sucesso', 'data' => $company], 200);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar empresa: ' . $e->getMessage());
            return response(['status' => 'error', 'message' => 'Ocorreu um erro ao atualizar a empresa. Por favor, tente novamente mais tarde.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $company = Company::find($id);
            
            // Verifica se a empresa existe
            if (!$company) {
                return response(['status' => 'error', 'message' => 'Empresa não encontrada'], 404);
            }

            // Exclui a empresa
            $company->delete();

            return response(['status' => 'success', 'message' => 'Empresa excluída com sucesso'], 200);
        } catch (Exception $e) {
            Log::error('Erro ao excluir empresa: ' . $e->getMessage());
            return response(['status' => 'error', 'message' => 'Ocorreu um erro ao excluir a empresa. Por favor, tente novamente mais tarde.'], 500);
        }
    }
}
