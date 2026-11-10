<?php

use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Account;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\RequestLogEntry;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Usage;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\UsageGrouping;

it('returns the account details', function () {
    $account = liveClient()->account()->details();

    expect($account)->toBeInstanceOf(Account::class)
        ->and($account->accountId())->not->toBe('')
        ->and($account->plan())->not->toBe('');
})->group('live-free');

it('returns the usage', function () {
    expect(liveClient()->account()->usage()->data())->toBeList()
        ->each->toBeInstanceOf(Usage::class);
})->group('live-free');

it('returns the usage grouped by endpoint', function () {
    expect(liveClient()->account()->usage(groupBy: [UsageGrouping::Endpoint])->data())->toBeList()
        ->each->toBeInstanceOf(Usage::class);
})->group('live-free');

it('returns the request log and its next page', function () {
    $account = liveClient()->account();

    $page = $account->requests(limit: 2);

    expect(count($page))->toBeLessThanOrEqual(2)
        ->and($page->data())->each->toBeInstanceOf(RequestLogEntry::class);

    $cursor = $page->meta()?->nextCursor();

    if ($cursor === null) {
        return;
    }

    $next = $account->requests(limit: 2, cursor: $cursor);

    expect($next->data())->each->toBeInstanceOf(RequestLogEntry::class);
})->group('live-free');

it('reads the request log lazily over several pages', function () {
    $account = liveClient()->account();
    $hasSecondPage = $account->requests(limit: 2)->meta()?->nextCursor() !== null;

    $entries = [];

    foreach ($account->lazy()->requests(limit: 2) as $key => $entry) {
        $entries[$key] = $entry;

        if (count($entries) === 6) {
            break;
        }
    }

    $requestIds = array_map(fn (RequestLogEntry $entry) => $entry->requestId(), $entries);

    expect($entries)->each->toBeInstanceOf(RequestLogEntry::class)
        ->and(array_keys($entries))->toBe(array_keys(array_values($entries)))
        ->and(array_unique($requestIds))->toBe($requestIds);

    if ($hasSecondPage) {
        expect(count($entries))->toBeGreaterThan(2);
    }
})->group('live-free');

it('reads the request log lazily with a status filter', function () {
    $entries = [];

    foreach (liveClient()->account()->lazy()->requests(status: 404, limit: 2) as $entry) {
        $entries[] = $entry;

        if (count($entries) === 4) {
            break;
        }
    }

    expect(array_map(fn (RequestLogEntry $entry) => $entry->status(), $entries))->each->toBe(404);
})->group('live-free');
