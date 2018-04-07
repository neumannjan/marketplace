<?php

namespace App\Console\Translation;


use Illuminate\Support\Arr;
use PhpParser\Node;
use PhpParser\NodeVisitor;

/**
 * NodeVisitor expecting a simple PHP file with a return statement and a nested associative array of strings.
 * This NodeVisitor is able to add or modify array nodes using an array of nested keys.
 */
class TranslationFileNodeVisitor implements NodeVisitor
{

    /**
     * Keys to search for
     * @var string[]
     */
    protected $keys;

    /** @var string|null */
    protected $newValue;

    /** @var int|null */
    protected $keysPosition = null;

    /** @var bool */
    protected $found = false;

    /**
     * TranslationFileNodeVisitor constructor.
     * @param string[] $keys
     * @param string|null $newValue Null if the node is supposed to be removed.
     */
    public function __construct(array $keys, $newValue)
    {
        $this->keys = $keys;
        $this->newValue = $newValue;
    }

    /**
     * @return string|null
     */
    protected function getCurrentKey()
    {
        return $this->keysPosition === null ? null : $this->keys[$this->keysPosition];
    }

    /**
     * @return string|null
     */
    protected function nextKey()
    {
        if ($this->keysPosition === null) {
            $this->keysPosition = 0;
        } elseif ($this->keysPosition + 1 >= count($this->keys)) {
            $this->keysPosition = null;
        } else {
            ++$this->keysPosition;
        }

        return $this->getCurrentKey();
    }

    /**
     * @return string|null
     */
    protected function previousKey()
    {
        if ($this->keysPosition === 0) {
            $this->keysPosition = null;
        } elseif ($this->keysPosition !== null) {
            --$this->keysPosition;
        }
    }

    /**
     * @return bool
     */
    protected function isKeyLast()
    {
        return $this->keysPosition >= count($this->keys) - 1;
    }

    public function beforeTraverse(array $nodes)
    {
        $this->found = false;
        $this->keysPosition = null;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\ArrayItem) {
            $key = $node->key->value;

            $currentKey = $this->getCurrentKey();
            $searchedKey = $currentKey === null ? $this->nextKey() : $currentKey;

            if ($key === $searchedKey) {
                if ($this->nextKey() === null) {
                    $this->found = true;

                    if ($this->newValue !== null) {
                        $node->value = new Node\Scalar\String_($this->newValue);
                    } else {
                        return null;
                    }

                }
            }
        }

        return $node;
    }

    public function leaveNode(Node $node)
    {
        $this->previousKey();
    }

    public function afterTraverse(array $nodes)
    {
        if (!$this->found && $this->newValue !== null) {
            /** @var Node\Expr\Array_ $node */
            $node = $nodes[0]->expr;

            $this->keysPosition = null;
            while (($key = $this->nextKey()) !== null) {
                /** @var Node\Expr\ArrayItem $arrayItem */
                $arrayItem = Arr::first($node->items, function ($node) use ($key) {
                    return ($node instanceof Node\Expr\ArrayItem) && $node->key && $node->key->value === $key;
                });

                if ($this->isKeyLast()) {
                    $val = new Node\Scalar\String_($this->newValue);
                } else {
                    if ($arrayItem && $arrayItem->value instanceof Node\Expr\Array_) {
                        $val = $arrayItem->value;
                    } else {
                        $val = new Node\Expr\Array_([]);
                    }
                }

                if ($arrayItem === null) {
                    $arrayItem = $node->items[] = new Node\Expr\ArrayItem($val, new Node\Scalar\String_($key));
                } else {
                    $arrayItem->value = $val;
                }

                $node = $arrayItem->value;
            }
        }
    }

}