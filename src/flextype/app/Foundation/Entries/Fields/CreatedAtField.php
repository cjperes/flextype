<?php

declare(strict_types=1);

/**
 * Flextype (https://flextype.org)
 * Founded by Sergey Romanenko and maintained by Flextype Community.
 */

use Flextype\Component\Filesystem\Filesystem;

$flextype->emitter->addListener('onEntryAfterInitialized', function () use ($flextype) {
    $flextype->entries->entry['created_at'] = isset($flextype->entries->entry['created_at']) ?
                                    (int) strtotime($flextype->entries->entry['created_at']) :
                                    (int) Filesystem::getTimestamp($flextype->entries->getFileLocation($flextype->entries->entry_path));
});

$flextype->emitter->addListener('onEntryCreate', function () use ($flextype) {
    if (isset($flextype->entries->entry_create_data['created_at'])) {
        $flextype->entries->entry_create_data['created_at'] = $flextype->entries->entry_create_data['created_at'];
    } else {
        $flextype->entries->entry_create_data['created_at'] = date($this->flextype->registry->get('flextype.settings.date_format'), time());
    }
});