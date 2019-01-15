<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector\Backend;

use PhpParser\Node;
use PhpParser\Node\Stmt as NodeType;
use PhpParser\NodeVisitorAbstract;

class PublicOnlyVisitor extends NodeVisitorAbstract {
    /**
     * @return int|Node
     */
    public function enterNode(Node $node) {
        if (($node instanceof NodeType\Property ||
                $node instanceof NodeType\ClassMethod ||
                $node instanceof NodeType\ClassConst) && !$node->isPublic()) {
            return new NodeType\Nop();
        }

        return $node;
    }
}
