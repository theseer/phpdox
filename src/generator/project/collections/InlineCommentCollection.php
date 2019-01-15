<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class InlineCommentCollection extends AbstractCollection {
    public function current(): MethodObject {
        return new InlineCommentObject($this->getCurrentNode());
    }
}
