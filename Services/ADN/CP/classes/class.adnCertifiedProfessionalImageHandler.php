<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Filesystem\Filesystems as Filesystem;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Exception\IllegalStateException;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Location;

/**
 * Image File handler
 * @author Stefan Meyer <meyer@leifos.de>
 */
class adnCertifiedProfessionalImageHandler
{
    protected const WEBDIR_PREFIX = 'pro_images';

    private ilLogger $logger;
    private $web_directory;


    private int $professional_id = 0;

    public function __construct(int $professional_id)
    {
        global $DIC;

        $this->professional_id = $professional_id;
        $this->logger = $DIC->logger()->adn();
        $this->web_directory = $DIC->filesystem()->web();
    }

    public function initWebDirectory() : void
    {
        if (!$this->web_directory->has(self::WEBDIR_PREFIX)) {
            try {
                $this->web_directory->createDir(self::WEBDIR_PREFIX);
            } catch (IOException $e) {
                $this->logger->error('Creating icon directory failed with message: ' . $e->getMessage());
            } catch (IllegalStateException $e) {
                $this->logger->warning('Creating icon directory failed with message: ' . $e->getMessage());
            }
        }
    }

    public function getAbsolutePath() : string
    {
        if ($this->web_directory->has(self::WEBDIR_PREFIX . '/' . 'pro_' . $this->getProfessionalId() . '.jpg')) {
            return ilUtil::getWebspaceDir() . '/' . self::WEBDIR_PREFIX . '/' . 'pro_' . $this->getProfessionalId(). '.jpg';
        }
        return '';
    }



    public function handleUpload(FileUpload $upload, string $tmpname) : void
    {
        if ($upload->hasUploads() && !$upload->hasBeenProcessed()) {
            try {
                $upload->process();
            } catch (IllegalStateException $e) {
                $this->logger->warning('File upload already processed: ' . $e->getMessage());
                return;
            }
        }
        $this->initWebDirectory();
        $result = isset($upload->getResults()[$tmpname]) ? $upload->getResults()[$tmpname] : false;
        if ($result instanceof UploadResult && $result->isOK() && $result->getSize()) {
            $this->delete();
            $upload->moveOneFileTo(
                $result,
                self::WEBDIR_PREFIX,
                Location::WEB,
                'pro_' . $this->getProfessionalId() . '.jpg'
            );
        }
    }

    public function delete()
    {
        if ($this->web_directory->has(self::WEBDIR_PREFIX . '/' . 'pro_' . $this->getProfessionalId()  . '.jpg')) {
            try {
                $this->web_directory->delete(self::WEBDIR_PREFIX . '/' . 'pro_'. $this->getProfessionalId() . '.jpg');
            } catch (Exception $e) {
                $this->logger->warning('Deleting icon failed with message: ' . $e->getMessage());
            }
        }
    }

    public function getProfessionalId() : int
    {
        return $this->professional_id;
    }

}