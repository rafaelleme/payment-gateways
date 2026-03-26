<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Repositories;

use Rafaelleme\PaymentGateways\Laravel\Models\GatewayCoupon;

class EloquentCouponRepository
{
    /**
     * Cria um novo cupom
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): GatewayCoupon
    {
        return GatewayCoupon::create($data);
    }

    /**
     * Atualiza um cupom existente
     *
     * @param int|string $id
     * @param array<string, mixed> $data
     */
    public function update($id, array $data): GatewayCoupon
    {
        $coupon = $this->findById($id);
        $coupon->update($data);

        return $coupon;
    }

    /**
     * Encontra um cupom por ID
     */
    public function findById(int|string $id): ?GatewayCoupon
    {
        return GatewayCoupon::find($id);
    }

    /**
     * Encontra um cupom por código
     */
    public function findByCode(string $code): ?GatewayCoupon
    {
        return GatewayCoupon::where('code', $code)->first();
    }

    /**
     * Encontra um cupom por código e gateway
     */
    public function findByCodeAndGateway(string $code, string $gateway): ?GatewayCoupon
    {
        return GatewayCoupon::where('code', $code)
            ->where('gateway', $gateway)
            ->first();
    }

    /**
     * Encontra um cupom por gateway_coupon_id
     */
    public function findByGatewayId(string $gateway, string $gatewayCouponId): ?GatewayCoupon
    {
        return GatewayCoupon::where('gateway', $gateway)
            ->where('gateway_coupon_id', $gatewayCouponId)
            ->first();
    }

    /**
     * Retorna todos os cupons ativos
     *
     * @return array<int, GatewayCoupon>
     */
    public function getActive(string $gateway = null): array
    {
        $query = GatewayCoupon::where('is_active', true);

        if ($gateway) {
            $query->where('gateway', $gateway);
        }

        return $query->get()->all();
    }

    /**
     * Retorna todos os cupons
     *
     * @return array<int, GatewayCoupon>
     */
    public function getAll(string $gateway = null): array
    {
        $query = GatewayCoupon::query();

        if ($gateway) {
            $query->where('gateway', $gateway);
        }

        return $query->get()->all();
    }

    /**
     * Deleta um cupom
     */
    public function delete(int|string $id): bool
    {
        $coupon = $this->findById($id);

        if (!$coupon) {
            return false;
        }

        return (bool) $coupon->delete();
    }

    /**
     * Verifica se um cupom existe
     */
    public function exists(string $code): bool
    {
        return GatewayCoupon::where('code', $code)->exists();
    }

    /**
     * Paginação de cupons
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function paginate(int $perPage = 15, string $gateway = null)
    {
        $query = GatewayCoupon::query();

        if ($gateway) {
            $query->where('gateway', $gateway);
        }

        return $query->paginate($perPage);
    }
}

