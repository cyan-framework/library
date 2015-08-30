<?php
namespace Cyan\Library;

/**
 * Class DatabaseTable
 * @package Cyan\Library
 */
class DatabaseTable
{
    use TraitsSingleton, TraitsContainer;

    /**
     * Find By
     *
     * @param array $condition
     */
    public function findID($where, $params)
    {
        $App = $this->getContainer('application');
        $Dbo = $App->Database->current;
        $Dbo->connect();

        /** @var array $table */
        $table = $this->getContainer('table');

        return $Dbo->findOne($table['table'],$where,$table['table_key'],$params);
    }
}