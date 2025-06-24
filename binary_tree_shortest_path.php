<?php
class TreeNode {
    public $val;
    public $left;
    public $right;

    public function __construct($val = 0, $left = null, $right = null) {
        $this->val = $val;
        $this->left = $left;
        $this->right = $right;
    }
}

class BinaryTree {
    private $root;

    public function __construct($root = null) {
        $this->root = $root;
    }

    // 使用BFS计算从根到最近叶子节点的最短路径
    public function minDepth() {
        if ($this->root === null) {
            return 0;
        }

        $queue = new SplQueue();
        $queue->enqueue([$this->root, 1]); // [节点, 层级]

        while (!$queue->isEmpty()) {
            list($node, $depth) = $queue->dequeue();

            // 如果是叶子节点，返回当前深度
            if ($node->left === null && $node->right === null) {
                return $depth;
            }

            // 将子节点加入队列
            if ($node->left !== null) {
                $queue->enqueue([$node->left, $depth + 1]);
            }
            if ($node->right !== null) {
                $queue->enqueue([$node->right, $depth + 1]);
            }
        }

        return 0; // 理论上不会执行到这里
    }
}

// 示例用法
// 构建测试二叉树:
//       3
//      / \
//     9  20
//       /  \
//      15   7
$root = new TreeNode(3);
$root->left = new TreeNode(9);
$root->right = new TreeNode(20);
$root->right->left = new TreeNode(15);
$root->right->right = new TreeNode(7);

$tree = new BinaryTree($root);
echo "最短路径长度: " . $tree->minDepth(); // 输出: 2
?>    