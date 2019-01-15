<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class TokenFileStartEvent extends AbstractEvent {
    /**
     * @var TokenFile
     */
    private $tokenFile;

    public function __construct(TokenFile $tokenFile) {
        $this->tokenFile = $tokenFile;
    }

    public function getTokenFile(): TokenFile {
        return $this->tokenFile;
    }

    protected function getEventName() {
        return 'token.file.start';
    }
}
