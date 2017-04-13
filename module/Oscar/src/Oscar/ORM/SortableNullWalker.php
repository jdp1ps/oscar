<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-03-24 16:42
 * @copyright Certic (c) 2016
 */

namespace Oscar\ORM;


use Doctrine\ORM\Query\SqlWalker;

class SortableNullWalker extends SqlWalker
{
    const NULLS_FIRST   = 'NULL FIRST';
    const NULLS_LAST    = 'NULL LAST';

    public function walkOrderByClause($orderByClause)
    {
        die('HERE !!!');
        $sql = parent::walkOrderByClause($orderByClause);
        if( $nullFields = $this->getQuery()->getHint('SortableNullsWalker.fields') ){
            if( is_array($nullFields) ){
                $platform = $this->getConnection()->getDatabasePlatform()->getName();
                switch ($platform)
                {
            case 'mysql':
                  // for mysql the nulls last is represented with - before the field name
                  foreach ($nullFields as $field => $sorting)
                  {
                      /**
                      NULLs are considered lower than any non-NULL value,
                      except if a - (minus) character is added before
                      the column name and ASC is changed to DESC, or DESC to ASC;
                      this minus-before-column-name feature seems undocumented.
                       */
                      if ('NULLS LAST' === $sorting)
                      {
                          $sql = preg_replace('/\s+([a-z0-9_]+)(\.' . $field . ') (ASC|DESC)?\s*/i', " ISNULL($1$2), $1$2 $3 ", $sql);
                      }
                  }
               break;

               case 'oracle':
               case 'postgresql':
                  foreach ($nullFields as $field => $sorting)
                  {
                      $sql = preg_replace('/(\.' . $field . ') (ASC|DESC)?\s*/i', "$1$2 " . $sorting, $sql);
                  }
               break;

               default:
                  // I don't know for other supported platforms.
               break;
            }
            }
        }
        return $sql;
    }

}