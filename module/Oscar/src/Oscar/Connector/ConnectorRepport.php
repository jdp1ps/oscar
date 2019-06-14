<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-05-24 12:30
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;


class ConnectorRepport
{
    private $notices;
    private $warnings;
    private $errors;
    private $added;
    private $updated;
    private $removed;

    private $start;
    private $end;


    public function getRepportStates(){
        return [
            'added' => $this->getAdded(),
            'updated' => $this->getUpdated(),
            'errors' => $this->getErrors(),
            'warnings' => $this->getWarnings(),
            'removed' => $this->getRemoved(),
            'notices' => $this->getNotices(),
        ];
    }

    /**
     * @return array
     */
    public function getNotices()
    {
        return $this->notices;
    }

    /**
     * @return array
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @return array
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return array
     */
    public function getRemoved()
    {
        return $this->removed;
    }

    public function isSuspect(){
        return count($this->getErrors()) + count($this->getWarnings()) > 0;
    }



    /**
     * ConnectorRepport constructor.
     */
    public function __construct()
    {
        $this->notices = [];
        $this->warnings = [];
        $this->errors = [];
        $this->added = [];
        $this->updated = [];
        $this->removed = [];
        $this->start();
    }

    public function start(){
        $this->start = time();
    }

    public function addnotice( $message ){
        $this->notices[] = [
            'time' => time(),
            'message' => '[.] ' . $message
        ];
    }

    public function addwarning( $message ){
        $this->warnings[] = [
            'time' => time(),
            'message' => '[~] ' . $message
        ];
    }

    public function adderror( $message ){
        $this->errors[] = [
            'time' => time(),
            'message' => '[!] ' . $message
        ];
    }

    public function addadded( $message ){
        $this->added[] = [
            'time' => time(),
            'message' => '[a] ' . $message
        ];
    }

    public function addupdated( $message ){
        $this->updated[] = [
            'time' => time(),
            'message' => '[u] ' . $message
        ];
    }

    public function addremoved( $message ){
        $this->removed[] = [
            'time' => time(),
            'message' => '[-] ' . $message
        ];
    }

    public function addRepport( ConnectorRepport $repport ){
        foreach ( $repport->getRepportStates() as $state=>$rep ){
            $this->$state = array_merge($this->$state, $rep);
        }
    }

    public function end(){
        $this->end = time();
    }

    public function getDuration(){
        if( !$this->end ){
            return time() - $this->start;
        }
        return $this->end - $this->start;
    }
}