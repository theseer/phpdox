<?php
namespace bar;

class foo {

    public function __construct(
        Request $request,
        array $roles,
        Router $router,
        FacebookHelper $facebookHelper,
        string $targetRoute = 'facebook_check',
        ?Logger $logger = null
    ) {
        parent::__construct($roles);
    }

}
