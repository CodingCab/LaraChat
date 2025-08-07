<?php

namespace App\Traits;

use App\Models\ModelTag;
use App\Models\Order;
use ArrayAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Tags\HasTags;
use Spatie\Tags\Tag;

/**
 * App\Models\Product.
 *
 * @method static withAnyTags($tags, $type = null)
 * @method static withAnyTagsOfAnyType($tags)
 */
trait HasTagsTrait
{
    use HasTags {
        attachTags as originalAttachTags;
        detachTags as originalDetachTags;
        scopeWithAllTags as traitHasTagsScopeWithAllTags;
    }

    public function scopeWithAllTags(Builder $query, $tags, ?string $type = null): Builder
    {
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }

        collect($tags)
            ->each(function ($tag) use ($query) {
                $query->where(function (Builder $query) use ($tag) {
                    $query->whereHas('modelTags', function (Builder $query) use ($tag) {
                        $query->where('tag_name', Str::lower($tag));
                    })->orWhereHas('tags', function (Builder $query) use ($tag) {
                        $query->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.\"en\"'))"), $tag);
                    });
                });
            });

        return $query;
    }

    public function scopeWithoutAllTags(Builder $query, $tags, ?string $type = null): Builder
    {
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }

        collect($tags)->each(function ($tag) use ($query, $type) {
            $query->where(function (Builder $query) use ($tag, $type) {
                $query->whereDoesntHave('modelTags', function (Builder $query) use ($tag, $type) {
                    $query->where('tag_name', Str::lower($tag));
                })->whereDoesntHave('tags', function (Builder $query) use ($tag, $type) {
                    $spatieTag = collect(static::convertToTags([$tag], $type))->first();
                    $query->where('tags.id', $spatieTag ? $spatieTag->id : 0);
                });
            });
        });

        return $query;
    }

    public function modelTags(): HasMany
    {
        return $this->hasMany(ModelTag::class, 'model_id', 'id')->where('model_type', static::class);
    }

    protected function onTagAttached($tag)
    {
        // override this function on model
    }

    protected function onTagDetached($tag)
    {
        // override this function on model
    }

    public function hasTags(?array $tags = null): bool
    {
        return static::withAllTags($tags)->whereId($this->getKey())->exists();
    }

    public function scopeHasTags(Builder $query, $tags): Builder
    {
        $tags = collect(func_get_args());
        $tags->shift();

        $tags = $tags->map(function ($tag) {
            return Str::lower($tag);
        });

        $query->where(function (Builder $query) use ($tags) {
            $tags->each(function ($tag) use ($query) {
                $query->whereHas('modelTags', function (Builder $query) use ($tag) {
                        $query->where('tag_name', $tag);
                })->orWhereHas('tags', function (Builder $query) use ($tag) {
                    $query->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.\"en\"'))"), $tag);
                });
            });
        });

        return $query;
    }

    public function attachTags(array $tags, ?string $type = null): self
    {
        collect($tags)
            ->filter()
            ->each(function ($tag) use ($type) {
                if ($this->hasTags([$tag])) {
                    return;
                }

                $this->originalAttachTags([$tag], $type);

                ModelTag::query()->updateOrCreate([
                    'model_type' => static::class,
                    'model_id' => $this->getKey(),
                    'tag_name' => Str::lower($tag),
                    'tag_type' => $type,
                ])
                ->update([
                    'updated_at' => now(),
                ]);

                if (in_array(LogsActivityTrait::class, class_uses_recursive($this))) {
                    $this->log('attached "'.$tag.'" tag');
                }

                $this->onTagAttached($tag);
            });

        return $this;
    }

    public function detachTags(array|Collection $tags, ?string $type = null): self
    {
        collect($tags)
            ->filter()
            ->each(function ($tag) use ($type) {
                if ($this->doesNotHaveTags([$tag])) {
                    return;
                }
                $this->originalDetachTags([$tag], $type);
                $this->onTagDetached($tag);

                ModelTag::query()->where([
                    'model_type' => static::class,
                    'model_id' => $this->getKey(),
                    'tag_name' => Str::lower($tag),
                    'tag_type' => $type,
                ])->delete();

                $this->log('detached "'.$tag.'" tag');
            });

        return $this;
    }

    /**
     * Detach a single tag and remove corresponding models_tags record
     */
    public function detachTag(string|Tag $tag, ?string $type = null): self
    {
        $this->detachTags([$tag], $type);

        return $this;
    }

    /**
     * @param  string|Tag  $tag
     */
    public function detachTagSilently($tag, ?string $type = null): self
    {
        activity()->withoutLogs(function () use ($tag, $type) {
            $this->detachTag($tag, $type);

            ModelTag::query()->where([
                'model_type' => static::class,
                'model_id' => $this->getKey(),
                'tag_name' => Str::lower($tag),
                'tag_type' => $type,
            ])->delete();
        });

        return $this;
    }

    public function doesNotHaveTags(?array $tags = null): bool
    {
        return ! $this->hasTags($tags);
    }

    public function syncTagByType(string $tagType, string $tagName): void
    {
        $tag = $this->tags()->where(['type' => $tagType])->first();

        if ($tag === null) {
            $this->attachTag($tagName, $tagType);

            return;
        }

        if ($tag->name === $tagName) {
            return;
        }

        $this->detachTag($tag);
        $this->attachTag($tagName, $tagType);
    }
}
