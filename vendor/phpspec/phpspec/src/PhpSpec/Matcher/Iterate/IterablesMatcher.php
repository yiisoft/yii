<?php

namespace PhpSpec\Matcher\Iterate;

use PhpSpec\Formatter\Presenter\Presenter;

final class IterablesMatcher
{
    /**
     * @var Presenter
     */
    private $presenter;

    
    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * @param array|\Traversable $subject
     * @param array|\Traversable $expected
     *
     * @throws \InvalidArgumentException
     * @throws SubjectElementDoesNotMatchException
     * @throws SubjectHasFewerElementsException
     * @throws SubjectHasMoreElementsException
     */
    public function match($subject, $expected, bool $strict = true): void
    {
        if (!$this->isIterable($subject)) {
            throw new \InvalidArgumentException('Subject value should be an array or implement \Traversable.');
        }

        if (!$this->isIterable($expected)) {
            throw new \InvalidArgumentException('Expected value should be an array or implement \Traversable.');
        }

        $expectedIterator = $this->createIteratorFromIterable($expected);

        $count = 0;
        foreach ($subject as $subjectKey => $subjectValue) {
            if (!$expectedIterator->valid()) {
                throw new SubjectHasMoreElementsException();
            }

            if ($subjectKey !== $expectedIterator->key() || !$this->valueIsEqual($subjectValue, $expectedIterator->current(), $strict)) {
                throw new SubjectElementDoesNotMatchException(
                    $count,
                    $this->presenter->presentValue($subjectKey),
                    $this->presenter->presentValue($subjectValue),
                    $this->presenter->presentValue($expectedIterator->key()),
                    $this->presenter->presentValue($expectedIterator->current())
                );
            }

            $expectedIterator->next();
            ++$count;
        }

        if ($expectedIterator->valid()) {
            throw new SubjectHasFewerElementsException();
        }
    }

    
    private function isIterable($variable): bool
    {
        return \is_array($variable) || $variable instanceof \Traversable;
    }

    /**
     * @param array|\Traversable $iterable
     */
    private function createIteratorFromIterable($iterable): \Iterator
    {
        if (\is_array($iterable)) {
            return new \ArrayIterator($iterable);
        }

        $iterator = new \IteratorIterator($iterable);
        $iterator->rewind();

        return $iterator;
    }

    private function valueIsEqual($expected, $value, bool $strict): bool
    {
        return $strict ? $expected === $value : $expected == $value;
    }
}
