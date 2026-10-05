<?php

namespace App\Services;

use App\Models\ContactChannel;
use App\Models\ContactTarget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ContactChannelResolver
{
    /**
     * Resolve active channels for the current Page / Service / Location context.
     *
     * Selection order follows docs/database-schema.md:
     * 1. matching targeted channels,
     * 2. active global channels without targets,
     * 3. default first,
     * 4. sort_order then ID.
     *
     * @param iterable<int, Model> $targets
     * @return Collection<int, ContactChannel>
     */
    public function resolve(iterable $targets = []): Collection
    {
        $contextPairs = collect($targets)
            ->filter(fn (mixed $target) => $target instanceof Model)
            ->map(function (Model $target): ?array {
                $supported = collect(ContactTarget::TARGET_TYPES)
                    ->contains(fn (array $meta) => $target instanceof $meta['model']);

                if (! $supported || ! $target->exists) {
                    return null;
                }

                return [
                    'type' => $target->getMorphClass(),
                    'id' => (int) $target->getKey(),
                ];
            })
            ->filter()
            ->unique(fn (array $pair) => $pair['type'] . ':' . $pair['id'])
            ->values();

        $channels = ContactChannel::query()
            ->active()
            ->with('targets')
            ->get();

        return $channels
            ->filter(function (ContactChannel $channel) use ($contextPairs): bool {
                $targets = $channel->targets;

                if ($targets->isEmpty()) {
                    return true;
                }

                if ($contextPairs->isEmpty()) {
                    return false;
                }

                return $targets->contains(function (ContactTarget $target) use ($contextPairs): bool {
                    return $contextPairs->contains(fn (array $pair) =>
                        $pair['type'] === $target->targetable_type
                        && $pair['id'] === (int) $target->targetable_id
                    );
                });
            })
            ->sort(function (ContactChannel $a, ContactChannel $b) use ($contextPairs): int {
                $aMatched = $this->matchesContext($a, $contextPairs);
                $bMatched = $this->matchesContext($b, $contextPairs);

                return [
                    $aMatched ? 0 : 1,
                    $a->is_default ? 0 : 1,
                    (int) $a->sort_order,
                    (int) $a->id,
                ] <=> [
                    $bMatched ? 0 : 1,
                    $b->is_default ? 0 : 1,
                    (int) $b->sort_order,
                    (int) $b->id,
                ];
            })
            ->values();
    }

    /**
     * @param Collection<int, array{type:string,id:int}> $contextPairs
     */
    private function matchesContext(ContactChannel $channel, Collection $contextPairs): bool
    {
        if ($contextPairs->isEmpty()) {
            return false;
        }

        return $channel->targets->contains(function (ContactTarget $target) use ($contextPairs): bool {
            return $contextPairs->contains(fn (array $pair) =>
                $pair['type'] === $target->targetable_type
                && $pair['id'] === (int) $target->targetable_id
            );
        });
    }
}
