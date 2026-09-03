<?php

namespace Pterodactyl\Tests\Integration\Services\Nodes;

use Illuminate\Support\Str;
use Pterodactyl\Models\Node;
use Pterodactyl\Tests\Integration\IntegrationTestCase;
use Pterodactyl\Services\Nodes\NodeIdentifierService;

class NodeIdentifierServiceTest extends IntegrationTestCase
{
    protected NodeIdentifierService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(NodeIdentifierService::class);
    }

    public function testGenerateIdentifierCreatesValidFormat(): void
    {
        $identifier = $this->service->generateIdentifier();

        $this->assertStringStartsWith('node-', $identifier);
        $this->assertEquals(27, strlen($identifier)); // "node-" + 22 chars
    }

    public function testEnsureIdentifierSetsIdentifierOnNode(): void
    {
        $location = \Pterodactyl\Models\Location::factory()->create();
        $node = Node::factory()->create(['location_id' => $location->id, 'node_identifier' => null]);

        $this->assertNull($node->getRawOriginal('node_identifier'));

        $result = $this->service->ensureIdentifier($node);

        $this->assertNotNull($result->node_identifier);
        $this->assertStringStartsWith('node-', $result->node_identifier);
    }

    public function testEnsureIdentifierDoesNotOverwriteExistingIdentifier(): void
    {
        $location = \Pterodactyl\Models\Location::factory()->create();
        $node = Node::factory()->create([
            'location_id' => $location->id,
            'node_identifier' => 'node-existing123456789',
        ]);

        $result = $this->service->ensureIdentifier($node);

        $this->assertEquals('node-existing123456789', $result->node_identifier);
    }

    public function testIdentifiersAreUnique(): void
    {
        $identifiers = [];
        for ($i = 0; $i < 100; $i++) {
            $identifiers[] = $this->service->generateIdentifier();
        }

        $this->assertEquals(100, count(array_unique($identifiers)));
    }

    public function testGetWingsConfigureCommandContainsNodeIdentifier(): void
    {
        $location = \Pterodactyl\Models\Location::factory()->create();
        $node = Node::factory()->create([
            'location_id' => $location->id,
        ]);

        $command = $this->service->getWingsConfigureCommand($node);

        $this->assertStringContainsString($node->node_identifier, $command);
        $this->assertStringContainsString('curl', $command);
        $this->assertStringContainsString('--token', $command);
    }
}
