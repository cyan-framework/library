<?php
namespace Cyan\Framework;

/**
 * Class DatabaseQueryTraitSpecialfield
 * @package Cyan\Framework
 * @since 1.0.0
 */
trait DatabaseQueryTraitSpecialfield
{
    /**
     * @var DatabaseSchema
     * @since 1.0.0
     */
    protected $schema;

    /**
     * @var string
     * @since 1.0.0
     */
    private $regex_special_field = '/(\w+(\.\w+))|(\w+\:\w+(\.\w+)+)/';

    /**
     * Custom code for special fields
     *
     * @param $field
     * @param $join_tables_search
     * @param $add_list
     * @param bool $use_form
     *
     * @since 1.0.0
     */
    private function specialField($field, &$join_tables_search, &$add_list, $use_form = false)
    {
        $this->schema = DatabaseSchema::getInstance();
        $from = !empty($this->table_alias) ? $this->table_alias : $this->table ;

        preg_match_all($this->regex_special_field, $field, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $special_field) {
                $field_parts = parse_url($special_field);
                if (count($field_parts) > 1) {
                    $scheme_table = $this->schema->isAlias($field_parts['scheme']) ? $this->schema->getTable($field_parts['scheme']) : $field_parts['scheme'] ;
                    $scheme_table_alias = $this->schema->getAlias($scheme_table);

                    $left_join_table = $scheme_table;
                    //if ($schemeTable != $schemeTableAlias) {
                    //    $leftJoinTable = sprintf('%s AS %s',$schemeTable,$schemeTableAlias);
                    //}

                    $back_reference_id = $this->schema->getBackReference($scheme_table, $this->table);
                    $reference_key = $this->schema->getReference($this->table,$scheme_table);
                    if (!in_array($scheme_table,$join_tables_search)) {
                        $join_tables_search[] = $scheme_table;
                        $this->leftJoin($left_join_table, sprintf('%s.%s = %s.%s',$scheme_table,$back_reference_id,$this->table,$reference_key));
                    }

                    $join_tables = explode('.', $field_parts['path']);
                    $join_tables_field = array_slice($join_tables,-2);
                    if ($this->schema->isAlias($join_tables_field[0])) {
                        $join_tables_field[0] = $this->schema->getTable($join_tables_field[0]);
                    }
                    $replace_field = implode('.',$join_tables_field);
                    unset($join_tables_field);
                    array_pop($join_tables); //remove last item
                    $parent_table = $scheme_table;
                    foreach ($join_tables as $join_table) {
                        $parent_table = $this->schema->isAlias($parent_table) ? $this->schema->getTable($parent_table) : $parent_table;
                        $join_table = $this->schema->isAlias($join_table) ? $this->schema->getTable($join_table) : $join_table ;
                        $join_table_alias = $this->schema->getAlias($join_table);
                        $left_join_table = $join_table;

                        $reference_key = $this->schema->getReference($parent_table,$join_table);
                        $back_reference_id = $this->schema->getBackReference($join_table, $parent_table);

                        if (!in_array($join_table,$join_tables_search)) {
                            $join_tables_search[] = $join_table;
                            $this->leftJoin($left_join_table, sprintf('%s.%s = %s.%s',$join_table,$back_reference_id,$parent_table,$reference_key));
                        }

                        $parent_table = $join_table_alias;
                    }

                    $add_list[] = str_replace($special_field,$replace_field,$field);
                } else {
                    $sParts = explode('.',$field_parts['path']);

                    $join_table = $sParts[0];
                    $left_join_table = $join_table;

                    if ($join_table != $from) {
                        if ($this->schema->isAlias($join_table)) {
                            $join_table_alias = $join_table;
                            $join_table = $this->schema->getTable($join_table);
                            $left_join_table = sprintf('%s AS %s',$join_table,$join_table_alias);
                        } else {
                            $join_table_alias = $this->schema->getAlias($join_table);
                        }
                        $back_reference_id = $this->schema->getBackReference($join_table, $this->table);
                        $reference_key = $this->schema->getReference($this->table,$join_table);

                        if (!in_array($join_table,$join_tables_search)) {
                            $join_tables_search[] = $join_table;
                            $this->leftJoin($left_join_table, sprintf('%s.%s = %s.%s',$join_table,$back_reference_id,$this->table,$reference_key));
                        }
                    }




                    $add_list[] = $field;
                }
            }
        } else {
            $add_list[] = $use_form ? sprintf('%s.%s', $from, trim($field)) : $field;
        }
    }
}