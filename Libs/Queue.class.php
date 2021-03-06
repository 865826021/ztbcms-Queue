<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Queue\Libs;

use Queue\Libs\Queues\DatabaseQueue;

abstract class Queue {

    /**
     * 停止的信息号
     */
    const SIGNAL_STOP = 1;

    /**
     * @var Queue
     */
    private static $queue;

    /**
     * @return Queue|DatabaseQueue
     */
    static function getInstance() {
        if (empty(static::$queue)) {
            //目前默认是DB
            return static::$queue = new DatabaseQueue();
        }

        return static::$queue;
    }

    /**
     * 添加
     *
     * @param string $queue
     * @param Job    $job
     * @return Job
     */
    abstract function push($queue = '', Job $job);

    /**
     * 取出
     *
     * @param string $queue
     * @return Job|null
     */
    abstract function pop($queue = '');

    /**
     * 获取该任务的属性数据
     *
     * @param Job $job
     * @return array
     */
    public function createPlayload($job) {
        $properties = get_class_vars(get_class($job));
        $ret = array();
        foreach ($properties as $key => $val) {
            $ret[$key] = $job->$key;
        }

        return $ret;
    }

    /**
     * @param string $job_id
     * @param string $name
     * @param string $queue
     * @param string $playload
     * @param int    $attempts
     * @param int    $reserved_at
     * @param int    $available_at
     * @param string $status
     * @internal param $data
     * @return Job
     */
    public function asJob(
        $job_id,
        $name,
        $queue = '',
        $playload = '',
        $attempts = 0,
        $reserved_at = 0,
        $available_at = 0,
        $status
    ) {

        $playload = json_decode($playload, true);

        $job = $this->instanceJob($name);
        $job->setAttempts($attempts);
        $job->setAvailableAt($available_at);
        $job->setId($job_id);
        $job->setPlayload($playload);
        $job->setQueue($queue);
        $job->setReservedAt($reserved_at);
        $job->setStatus($status);

        //properties
        $properties = get_class_vars(get_class($job));
        foreach ($properties as $key => $val) {
            $job->$key = $playload[$key];
        }

        return $job;
    }

    /**
     *实例化Job类
     *
     * @param $name
     * @return Job
     */
    protected function instanceJob($name) {
        return new $name;
    }

    /**
     * 标识任务状态
     *
     * @param Job $job
     * @param int $status
     */
    abstract public function markAs($job, $status);

    /**
     * 删除任务
     *
     * @param $id
     * @return mixed
     */
    abstract public function deleteJob($id);

    /**
     * 把Job重新
     * @param string $queue
     * @param Job    $job
     * @return mixed
     */
    abstract public function release($queue = '', Job $job);

}