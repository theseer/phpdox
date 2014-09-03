<?php
namespace TheSeer\phpDox\Generator {

    class TokenFileStartEvent extends AbstractEvent {

        /**
         * @var TokenFile
         */
        private $tokenFile;

        public function __construct(TokenFile $tokenFile) {
            $this->tokenFile = $tokenFile;
        }

        /**
         * @return TokenFile
         */
        public function getTokenFile() {
            return $this->tokenFile;
        }

        protected function getEventName() {
            return 'token.file.start';
        }

    }

}
