<?php


namespace inisire\ReactBundle\Threaded;


use Symfony\Component\HttpKernel\KernelInterface;

abstract class Task extends \Threaded
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \Volatile
     */
    protected $result;

    /**
     * @var bool
     */
    protected $completed = false;

    public function __construct()
    {
        $this->result = new \Volatile();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return (array) $this->result;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * @return bool
     */
    public function isGarbage() : bool
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }

    /**
     * Can be called only in task's run phase
     *
     * @return KernelInterface
     */
    public function getKernel()
    {
        if ($this->worker instanceof Worker) {
            return $this->worker->getKernel();
        } else {
            throw new \RuntimeException('Bad worker for task or wrong getKernel() call');
        }
    }

    /**
     * @return void
     */
    abstract protected function doRun();

    /**
     * Wrap unsafe doRun
     *
     * @return void
     */
    public function run()
    {
//        $logger = $this->getKernel()->getContainer()->get('logger');

//        try {

            $this->doRun();
            $this->completed = true;

//        } catch (\Exception $exception) {
//
//            $logger->error(sprintf('Uncaught exception "%s" with message "%s" in %s:%s',
//                get_class($exception),
//                $exception->getMessage(),
//                $exception->getFile(),
//                $exception->getLine()
//            ));
//
//        } catch (\Error $error) {
//
//            $logger->error(sprintf('Uncaught exception "%s" with message "%s" in %s:%s',
//                get_class($error),
//                $error->getMessage(),
//                $error->getFile(),
//                $error->getLine()
//            ));

//        } finally {
//

//
//        }

    }
}