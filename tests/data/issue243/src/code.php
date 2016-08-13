<?php

class a {}

trait traitB {
    // this trait is deliberatly missing
    use traitA;
}

trait traitC {
    use traitB;
}

class b extends a {
    use traitC;
}
