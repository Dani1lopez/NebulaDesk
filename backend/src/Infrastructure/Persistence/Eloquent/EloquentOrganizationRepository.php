<?php

namespace NebulaDesk\Infrastructure\Persistence\Eloquent;

use App\Models\Organization as EloquentOrganization;
use NebulaDesk\Domain\Entities\Organization;
use NebulaDesk\Domain\Repositories\OrganizationRepositoryInterface;

class EloquentOrganizationRepository implements OrganizationRepositoryInterface
{
    public function findAll(): array
    {
        $eloquentOrgs = EloquentOrganization::all();
        return $eloquentOrgs->map(fn($org) => $this->toDomain($org))->toArray();
    }

    public function findById(int $id): ?Organization
    {
        $eloquentOrg = EloquentOrganization::find($id);

        if (!$eloquentOrg) {
            return null;
        }

        return $this->toDomain($eloquentOrg);
    }

    public function save(Organization $organization): Organization
    {
        $eloquentOrg = EloquentOrganization::updateOrCreate(
            ['id' => $organization->id],
            [
                'name' => $organization->name,
                'domain' => $organization->domain,
                'is_active' => $organization->isActive,
            ]
        );

        return $this->toDomain($eloquentOrg);
    }

    public function update(Organization $organization): Organization
    {
        return $this->save($organization);
    }

    public function delete(int $id): bool
    {
        $eloquentOrg = EloquentOrganization::find($id);

        if (!$eloquentOrg) {
            return false;
        }

        // Soft delete - sets deleted_at timestamp, preserves data
        return $eloquentOrg->delete();
    }

    private function toDomain(EloquentOrganization $eloquentOrg): Organization
    {
        return new Organization(
            id: $eloquentOrg->id,
            name: $eloquentOrg->name,
            domain: $eloquentOrg->domain,
            isActive: $eloquentOrg->is_active ?? true,
            createdAt: \DateTimeImmutable::createFromMutable($eloquentOrg->created_at),
            updatedAt: \DateTimeImmutable::createFromMutable($eloquentOrg->updated_at),
            deletedAt: $eloquentOrg->deleted_at ? \DateTimeImmutable::createFromMutable($eloquentOrg->deleted_at) : null,
        );
    }
}
