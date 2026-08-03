<?php

namespace Modules\RSSFeedSettings\Entities;

use Illuminate\Database\Eloquent\Model;

class AiIntelNewsLog extends Model
{
    protected $table = 'ai_intel_news_logs';

    protected $fillable = [
        'code',
        'title',
        'source',
        'source_url',
        'category',
        'executive_summary',
        'intelligence_context',
        'indicators_json',
        'vulnerabilities_json',
        'published_at',
        'status',
        'transaction_rss_id',
        'rss_news_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function get_rss_news()
    {
        return $this->belongsTo(RSSNews::class, 'transaction_rss_id', 'transaction_rss_id')
            ->where('transaction_rss_id', '!=', null);
    }

    public function transaction_rss()
    {
        return $this->belongsTo(TransactionRssData::class, 'transaction_rss_id', 'id');
    }
}
