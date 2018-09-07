<?php

namespace AmcLab\Baseline\Output;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Output {

	protected $output;
	protected $logger;

    public function __construct(OutputInterface $output, LoggerInterface $logger) {

        $this->output = $output;
        $this->logger = $logger;

    }

    public function writeln(string $message, string $prepend = null, string $tag = null) {
        // 🚫 ✅ ❓ 🔔 🤖 🍺 💶 📦

        $out = [];
        $out[] = $prepend ? "$prepend " : "";
        $out[] = $tag ? "<$tag>" : "";
        $out[] = $message;
        $out[] = $tag ? "</>" : "";

        $this->output->writeln(implode($out));

    }

    public function linebreak() {
        $this->writeln('');
    }

    public function info(string $message) {
        $this->writeln($message, "👀", 'info');
    }

    public function comment(string $message) {
        $this->writeln($message, "✅", 'comment');
    }

    public function question(string $message) {
        $this->writeln($message, "🔍", 'question');
    }

    public function error(string $message) {
        $this->writeln($message, "❌", 'error');
    }

    public function warning(string $message) {
        $this->writeln($message, "❗️", 'bg=yellow;fg=black;blink');
    }

    public function debug($data, $always = false) {
        if (env('APP_DEBUG') || $always) {
            $this->question('DEBUG DUMP:');
            dump($data);
        }
    }

    public function wtf(\Throwable $e, $data = null) {
        // scrivo sul log
        $this->logger->error($e);

        // stampo su schermo
        $this->warning('Logging and notifying "'.get_class($e).'" to devs!');
        $this->debug($e, true);

        // TODO: ... mandare tutta l'eccezione e $data agli sviluppatori
    }

}

