<?php

use App\Models\Threat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('regular users cannot import threat records or download import templates', function () {
    $this->post(route('threatmetrics.import'))->assertForbidden();
    $this->get(route('threatmetrics.import.template', ['format' => 'csv']))->assertForbidden();
});

function makeThreat(array $overrides = []): Threat
{
    return Threat::create(array_merge([
        'date' => '2026-11-20 10:00:00',
        'user' => 'PhishStats',
        'type' => 'domain',
        'value' => 'evil.example.com',
        'tags' => ['#phishing'],
        'tweet' => null,
    ], $overrides));
}

test('datatable endpoint filters by search and type', function () {
    makeThreat(['value' => 'evil.example.com']);
    makeThreat(['type' => 'ip', 'value' => '45.33.22.11', 'tags' => ['#botnet']]);

    $this->getJson(route('threatmetrics.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'search' => ['value' => 'botnet'],
    ]))
        ->assertOk()
        ->assertJsonPath('recordsTotal', 2)
        ->assertJsonPath('recordsFiltered', 1)
        ->assertJsonPath('data.0.type', 'IP');

    $this->getJson(route('threatmetrics.data', ['draw' => 1, 'type' => 'domain']))
        ->assertOk()
        ->assertJsonPath('recordsFiltered', 1);
});

test('import reports berhasil, ditolak, and tidak valid per row', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $csv = implode("\n", [
        'date,user,type,value,tags',
        '2026-11-20 10:00:00,PhishStats,domain,evil.com,"#a, #b"',
        '2026-11-20 10:00:00,PhishStats,domain,evil.com,"#a, #b"',
        'bukan-tanggal,PhishStats,ip,1.1.1.1,',
    ]);

    $this->post(route('threatmetrics.import'), [
        'file' => UploadedFile::fake()->createWithContent('threats.csv', $csv),
    ])->assertRedirect(route('threatmetrics.index'))
        ->assertSessionHas('import_report', fn (array $report) => $report['summary']['berhasil'] === 1
            && $report['summary']['ditolak'] === 1
            && $report['summary']['tidak valid'] === 1);

    expect(Threat::count())->toBe(1);
    expect(Threat::first()->threat_id)->toBe('TH-0001');
});

test('import rejects a file without the required columns', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->post(route('threatmetrics.import'), [
        'file' => UploadedFile::fake()->createWithContent('threats.csv', "foo,bar\n1,2"),
    ])->assertRedirect(route('threatmetrics.index'))
        ->assertSessionHas('error');

    expect(Threat::count())->toBe(0);
});

test('import rejects unsupported file types', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->post(route('threatmetrics.import'), [
        'file' => UploadedFile::fake()->create('threats.pdf', 10, 'application/pdf'),
    ])->assertSessionHasErrors('file');
});

test('csv export neutralizes formulas and follows filters', function () {
    makeThreat(['value' => 'evil.example.com', 'tweet' => '=HYPERLINK("http://x")']);
    makeThreat(['type' => 'ip', 'value' => '45.33.22.11']);

    $response = $this->get(route('threatmetrics.export', ['format' => 'csv', 'type' => 'domain']));

    $response->assertOk();

    $content = $response->streamedContent();

    expect($content)->toContain('evil.example.com')
        ->toContain("'=HYPERLINK")
        ->not->toContain('45.33.22.11');
});

test('spss export returns a zip', function () {
    makeThreat();

    $response = $this->get(route('threatmetrics.export', ['format' => 'spss']));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('zip');
});

test('export redirects with an error when nothing matches', function () {
    $this->get(route('threatmetrics.export', ['format' => 'xlsx']))
        ->assertRedirect(route('threatmetrics.index'))
        ->assertSessionHas('error');
});
