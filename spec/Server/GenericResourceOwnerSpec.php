<?php

namespace spec\League\OAuth1\Client\Server;

use League\OAuth1\Client\Server\GenericResourceOwner;
use PhpSpec\ObjectBehavior;

class GenericResourceOwnerSpec extends ObjectBehavior
{
    private $payload;

    public function let()
    {
        $this->payload = ['id' => 123, 'name' => 'Ben'];

        $this->beConstructedWith($this->payload);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(GenericResourceOwner::class);
    }

    public function it_correctly_returns_an_id()
    {
        $this->getId()->shouldBe($this->payload['id']);
    }

    public function it_returns_no_id_if_one_isnt_set()
    {
        $this->beConstructedWith([]);

        $this->getId()->shouldBeNull();
    }

    public function it_can_be_represented_as_an_array()
    {
        $this->toArray()->shouldBe($this->payload);
    }

    public function it_is_an_iterator_aggregate()
    {
        $this->getIterator()->shouldBe($this->payload);
    }
}
