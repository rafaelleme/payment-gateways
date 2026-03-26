<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rafaelleme\PaymentGateways\Laravel\Repositories\EloquentCouponRepository;

class CouponController
{
    public function __construct(
        protected EloquentCouponRepository $couponRepository,
    ) {
    }

    /**
     * Retorna lista de cupons com paginação
     */
    public function index(Request $request): JsonResponse
    {
        $gateway = $request->query('gateway');
        $perPage = (int) $request->query('per_page', 15);

        $coupons = $this->couponRepository->paginate($perPage, $gateway);

        return response()->json($coupons);
    }

    /**
     * Retorna um cupom específico
     */
    public function show(int|string $id): JsonResponse
    {
        $coupon = $this->couponRepository->findById($id);

        if (!$coupon) {
            return response()->json(
                ['message' => 'Cupom não encontrado'],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json($coupon);
    }

    /**
     * Cria um novo cupom
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'       => 'required|string|unique:gateway_coupons',
            'gateway'    => 'required|string',
            'type'       => 'required|in:percentage,fixed_amount',
            'value'      => 'required|numeric|min:0',
            'currency'   => 'string|default:BRL',
            'max_uses'   => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'is_active'  => 'boolean|default:true',
            'metadata'   => 'nullable|array',
        ]);

        // Validação de tipo específica
        if ($validated['type'] === 'percentage') {
            if ((float) $validated['value'] < 0 || (float) $validated['value'] > 100) {
                return response()->json(
                    ['message' => 'Percentual deve estar entre 0 e 100'],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        $coupon = $this->couponRepository->create($validated);

        return response()->json($coupon, Response::HTTP_CREATED);
    }

    /**
     * Atualiza um cupom existente
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $coupon = $this->couponRepository->findById($id);

        if (!$coupon) {
            return response()->json(
                ['message' => 'Cupom não encontrado'],
                Response::HTTP_NOT_FOUND
            );
        }

        $validated = $request->validate([
            'code'       => 'string|unique:gateway_coupons,code,' . $id,
            'type'       => 'in:percentage,fixed_amount',
            'value'      => 'numeric|min:0',
            'currency'   => 'string',
            'max_uses'   => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'is_active'  => 'boolean',
            'metadata'   => 'nullable|array',
        ]);

        // Validação de tipo específica
        if (isset($validated['type']) && isset($validated['value'])) {
            if ($validated['type'] === 'percentage') {
                if ((float) $validated['value'] < 0 || (float) $validated['value'] > 100) {
                    return response()->json(
                        ['message' => 'Percentual deve estar entre 0 e 100'],
                        Response::HTTP_UNPROCESSABLE_ENTITY
                    );
                }
            }
        }

        $updated = $this->couponRepository->update($id, $validated);

        return response()->json($updated);
    }

    /**
     * Deleta um cupom
     */
    public function destroy(int|string $id): JsonResponse
    {
        $coupon = $this->couponRepository->findById($id);

        if (!$coupon) {
            return response()->json(
                ['message' => 'Cupom não encontrado'],
                Response::HTTP_NOT_FOUND
            );
        }

        $this->couponRepository->delete($id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Busca cupom por código
     */
    public function findByCode(string $code): JsonResponse
    {
        $coupon = $this->couponRepository->findByCode($code);

        if (!$coupon) {
            return response()->json(
                ['message' => 'Cupom não encontrado'],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json($coupon);
    }

    /**
     * Retorna cupons ativos de um gateway
     */
    public function activeByGateway(string $gateway): JsonResponse
    {
        $coupons = $this->couponRepository->getActive($gateway);

        return response()->json(['data' => $coupons]);
    }

    /**
     * Valida se um cupom é válido para uso
     */
    public function validate(string $code, string $gateway = null): JsonResponse
    {
        if ($gateway) {
            $coupon = $this->couponRepository->findByCodeAndGateway($code, $gateway);
        } else {
            $coupon = $this->couponRepository->findByCode($code);
        }

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Cupom não encontrado',
            ]);
        }

        $isValid = $coupon->isValid();

        return response()->json([
            'valid' => $isValid,
            'coupon' => $coupon,
            'message' => $isValid ? 'Cupom válido' : 'Cupom inválido',
        ]);
    }
}

