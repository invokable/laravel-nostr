<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native\Concerns;

use Revolution\Nostr\Filter;
use swentel\nostr\Filter\Filter as NativeFilter;

trait HasFilter
{
    protected function toNativeFilter(Filter $filter): NativeFilter
    {
        $n_filter = new NativeFilter();

        if (! empty($filter->ids)) {
            $n_filter->setIds($filter->ids);
        }

        if (! empty($filter->authors)) {
            $n_filter->setAuthors($filter->authors);
        }

        if (! empty($filter->kinds)) {
            $n_filter->setKinds($filter->kinds);
        }

        if (! empty($filter->limit)) {
            $n_filter->setLimit($filter->limit);
        }

        if (! empty($filter->since)) {
            $n_filter->setSince($filter->since);
        }

        if (! empty($filter->until)) {
            $n_filter->setUntil($filter->until);
        }

        if (! empty($etag = $filter->toArray()['#e'] ?? null)) {
            $n_filter->setLowercaseETags($etag);
        }

        if (! empty($ptag = $filter->toArray()['#p'] ?? null)) {
            $n_filter->setLowercasePTags($ptag);
        }

        return $n_filter;
    }
}
