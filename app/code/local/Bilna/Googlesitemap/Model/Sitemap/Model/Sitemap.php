<?php
class Bilna_Googlesitemap_Model_Sitemap_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{
    protected $property_limit = 50000;

    public function generateXml()
    {
        $count_of_property = 1;
        $number_of_file = 1;
        $io = $this->openXML($number_of_file++);
        $storeId = $this->getStoreId();
        $date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        /**
         * Generate categories sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/category/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );

            if ($count_of_property++ > $this->property_limit) {
                $count_of_property = 1;
                $this->closeXML($io);
                $io = $this->openXML($number_of_file++);
            }

            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate products sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/product/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );

            if ($count_of_property++ > $this->property_limit) {
                $count_of_property = 1;
                $this->closeXML($io);
                $io = $this->openXML($number_of_file++);
            }

            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/page/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );

            if ($count_of_property++ > $this->property_limit) {
                $count_of_property = 1;
                $this->closeXML($io);
                $io = $this->openXML($number_of_file++);
            }

            $io->streamWrite($xml);
        }
        unset($collection);

        $this->closeXML($io);

        $this->resetFilename($number_of_file);

        return $this;
    }

    public function openXML($number_of_file)
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));

        $filename = $this->generateFilename($number_of_file);

        if ($io->fileExists($filename) && !$io->isWriteable($filename)) {
            Mage::throwException(Mage::helper('sitemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $filename, $this->getPath()));
        }

        $io->streamOpen($filename);

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        return $io;
    }

    public function closeXML($io)
    {
        $io->streamWrite('</urlset>');
        $io->streamClose();

        return true;
    }

    public function generateFilename($number_of_file)
    {
        $filename = $this->getSitemapFilename();
        $filename = str_replace(".xml", "", $filename);
        $filename = ($number_of_file > 1) ? str_replace("_" . ($number_of_file-1), "", $filename) : $filename;
        $filename .= '_' . $number_of_file . '.xml';
        $this->setSitemapFilename($filename);

        return $filename;
    }

    public function resetFilename($number_of_file = "")
    {
        $filename = $this->getSitemapFilename();
        if ($number_of_file != "" && $number_of_file > 1) {
            $filename = str_replace("_" . ($number_of_file - 1), "", $filename);
        }
        $this->setSitemapFilename($filename);
        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return true;
    }
}