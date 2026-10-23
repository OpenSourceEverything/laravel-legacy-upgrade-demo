<?php

return [
    // Preserve bcrypt hashes already stored by the 5.3 application.
    'driver' => 'bcrypt',
    'bcrypt' => ['rounds' => 10],
];
