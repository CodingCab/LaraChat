<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int $order_id
 * @property string $shipping_number
 * @property string $carrier
 * @property string $service
 * @property string $tracking_url
 * @property string $content_type
 * @property string|null $base64_pdf_labels
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 * @property-read User|null $user
 */
class ShippingLabel extends Model
{
    use HasFactory;

    const CONTENT_TYPE_URL = 'url';

    const CONTENT_TYPE_PDF = 'pdf';

    const CONTENT_TYPE_RAW = 'raw';

    protected $table = 'orders_shipments';

    /**
     * @var string[]
     */
    protected $fillable = [
        'order_id',
        'user_id',
        'carrier',
        'service',
        'shipping_number',
        'tracking_url',
        'document_id',
        'content_type',
        'base64_pdf_labels',
    ];

    // we use attributes to set default values
    // we won't use database default values
    // as this is then not populated
    // correctly to events
    protected $attributes = [
        'content_type' => 'raw',
        'base64_pdf_labels' => '',
    ];

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(ShippingLabel::class)
            ->allowedFilters([
                AllowedFilter::partial('shipping_number'),
                AllowedFilter::exact('order.status_code'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('order_id'),

                AllowedFilter::scope('age_in_days_between', 'whereAgeInDaysBetween'),
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes([
                'order',
                'user',
            ])
            ->defaultSort('-id')
            ->allowedSorts([
                'id',
            ]);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
