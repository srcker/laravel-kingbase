<?php

namespace srcker\Kingbase\Database\Query\Grammars;
use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Query\JoinLateralClause;
use Illuminate\Support\Collection;
use RuntimeException;

class KingbaseGrammar extends BaseGrammar
{
    /**
     * 包装表名
     */
    public function wrapTable($table)
    {
        $schema = $this->connection->getConfig('schema') ?? '';

        // 已经包含 schema 或带括号的复杂表达式直接返回
        if (str_contains($table, '.') || str_starts_with($table, '(')) {
            return parent::wrapTable($table);
        }

        return $schema ? sprintf('"%s"."%s"', $schema, $table) : sprintf('"%s"', $table);
    }

    /**
     * 包装字段
     */
    public function wrap($value)
    {
        // 字段可能带表名
        if (str_contains($value, '.')) {
            [$table, $column] = explode('.', $value, 2);
            $schema = $this->connection->getConfig('schema') ?? '';

            // 如果 table 没有双引号，添加 schema
            if (!str_contains($table, '"')) {
                $table = $schema ? sprintf('"%s"."%s"', $schema, $table) : sprintf('"%s"', $table);
            }

            return $table . '."' . $column . '"';
        }

        return parent::wrap($value);
    }

    /**
     * JOIN 编译
     */
    protected function compileJoins(Builder $query, $joins)
    {
        return collect($joins)->map(function (JoinClause $join) use ($query) {
            $table = $this->wrapTable($join->table);

            $nestedJoins = $join->joins ? ' '.$this->compileJoins($query, $join->joins) : '';

            $tableAndNested = $join->joins ? '('.$table.$nestedJoins.')' : $table;

            if ($join instanceof JoinLateralClause) {
                return $this->compileJoinLateral($join, $tableAndNested);
            }

            return trim("{$join->type} join {$tableAndNested} {$this->compileWheres($join)}");
        })->implode(' ');
    }

    /**
     * FROM 编译
     */
    protected function compileFrom(Builder $query, $table)
    {
        return 'from '.$this->wrapTable($table);
    }


}