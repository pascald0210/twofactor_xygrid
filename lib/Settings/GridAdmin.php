<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IURLGenerator;
use OCP\Settings\ISettings;

final class GridAdmin implements ISettings {
    public function __construct(private IURLGenerator $urls) {}
    public function getSection(): string { return 'security'; }
    public function getPriority(): int { return 65; }
    public function getForm(): TemplateResponse {
        return new TemplateResponse('twofactor_xygrid', 'grid_admin', [
            'lookup' => $this->urls->linkToRoute('twofactor_xygrid.gridAdmin.lookup'),
            'unlock' => $this->urls->linkToRoute('twofactor_xygrid.gridAdmin.unlock'),
        ], '');
    }
}
