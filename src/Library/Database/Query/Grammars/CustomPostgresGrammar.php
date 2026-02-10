<?php

namespace Qscmf\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\PostgresGrammar;

/**
 * 自定义 PostgreSQL Grammar
 * 解决：表别名自动加前缀导致 selectRaw 中的引用错误
 */
class CustomPostgresGrammar extends PostgresGrammar
{
    /**
     * 是否给表别名加前缀
     *
     * @var bool
     */
    protected $prefixTableAliases = false;

    /**
     * 设置是否给表别名加前缀
     *
     * @param bool $value
     * @return $this
     */
    public function setPrefixTableAliases($value)
    {
        $this->prefixTableAliases = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * 覆盖父类方法，根据配置决定是否给别名加前缀
     */
    protected function wrapAliasedTable($value)
    {
        $segments = preg_split('/\s+as\s+/i', $value);

        // 如果配置不给别名加前缀，则直接使用原别名
        if (!$this->prefixTableAliases) {
            return $this->wrapTable($segments[0]) . ' as ' . $this->wrapValue($segments[1]);
        }

        // 否则使用父类的默认行为（加前缀）
        return parent::wrapAliasedTable($value);
    }
}
