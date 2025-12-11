<?php

namespace Modules\SmsCore\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Service for importing phone numbers from files
 * Supports CSV and Excel formats
 */
class FileImportService
{
    /**
     * Import phone numbers from uploaded file
     * 
     * @param array $file PHP $_FILES array element
     * @return array Array of phone numbers
     * @throws \Exception If file processing fails
     */
    public static function import(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Erreur lors du téléchargement du fichier: ' . self::getUploadErrorMessage($file['error']));
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        switch ($extension) {
            case 'csv':
                return self::importCSV($file['tmp_name']);

            case 'xlsx':
            case 'xls':
                return self::importExcel($file['tmp_name'], $extension);

            default:
                throw new \Exception('Format de fichier non supporté: ' . $extension);
        }
    }

    /**
     * Import from CSV file
     * 
     * @param string $filePath Path to CSV file
     * @return array Array of phone numbers
     */
    private static function importCSV(string $filePath): array
    {
        $numbers = [];
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \Exception('Impossible d\'ouvrir le fichier CSV');
        }

        $isFirstRow = true;

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            // Skip header row if it looks like a header
            if ($isFirstRow) {
                $isFirstRow = false;
                // Check if first cell looks like a phone number
                if (!empty($data[0]) && !self::looksLikePhoneNumber($data[0])) {
                    continue; // Skip header
                }
            }

            // First column should contain phone number
            if (!empty($data[0])) {
                $number = self::cleanPhoneNumber($data[0]);
                if ($number) {
                    $numbers[] = $number;
                }
            }
        }

        fclose($handle);

        return array_unique($numbers);
    }

    /**
     * Import from Excel file
     * 
     * @param string $filePath Path to Excel file
     * @param string $extension File extension (xlsx or xls)
     * @return array Array of phone numbers
     */
    private static function importExcel(string $filePath, string $extension): array
    {
        // Check if PhpSpreadsheet is available
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            // Fallback: treat as CSV
            return self::importCSV($filePath);
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $numbers = [];

            $isFirstRow = true;

            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }

                // Skip header row
                if ($isFirstRow) {
                    $isFirstRow = false;
                    if (!empty($cells[0]) && !self::looksLikePhoneNumber($cells[0])) {
                        continue;
                    }
                }

                // First column should contain phone number
                if (!empty($cells[0])) {
                    $number = self::cleanPhoneNumber($cells[0]);
                    if ($number) {
                        $numbers[] = $number;
                    }
                }
            }

            return array_unique($numbers);
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la lecture du fichier Excel: ' . $e->getMessage());
        }
    }

    /**
     * Check if a string looks like a phone number
     * 
     * @param mixed $value Value to check
     * @return bool
     */
    private static function looksLikePhoneNumber($value): bool
    {
        if (is_numeric($value)) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        // Remove common phone number characters
        $cleaned = preg_replace('/[\s\-\(\)\+]/', '', $value);

        // Check if mostly digits
        return strlen($cleaned) >= 6 && preg_match('/^\d+$/', $cleaned);
    }

    /**
     * Clean and normalize phone number
     * 
     * @param mixed $value Raw value from file
     * @return string|null Cleaned phone number or null if invalid
     */
    private static function cleanPhoneNumber($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Convert to string
        $number = (string)$value;

        // Remove whitespace, dashes, parentheses
        $number = preg_replace('/[\s\-\(\)]/', '', $number);

        // Keep only digits and plus sign
        $number = preg_replace('/[^\d\+]/', '', $number);

        // Must have at least 6 digits
        if (strlen(preg_replace('/[^\d]/', '', $number)) < 6) {
            return null;
        }

        return $number;
    }

    /**
     * Get human-readable upload error message
     * 
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private static function getUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'Le fichier est trop volumineux';
            case UPLOAD_ERR_PARTIAL:
                return 'Le fichier n\'a été que partiellement téléchargé';
            case UPLOAD_ERR_NO_FILE:
                return 'Aucun fichier n\'a été téléchargé';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Dossier temporaire manquant';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Échec de l\'écriture du fichier sur le disque';
            case UPLOAD_ERR_EXTENSION:
                return 'Une extension PHP a arrêté le téléchargement';
            default:
                return 'Erreur inconnue';
        }
    }

    /**
     * Import file with full column detection and data extraction
     * Returns array with headers and all data rows
     *
     * @param array $file PHP $_FILES array element
     * @return array ['headers' => [...], 'data' => [[...], [...]]]
     * @throws \Exception If file processing fails
     */
    public static function importWithColumns(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Erreur lors du téléchargement du fichier: ' . self::getUploadErrorMessage($file['error']));
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        switch ($extension) {
            case 'csv':
                return self::importCSVWithColumns($file['tmp_name']);

            case 'xlsx':
            case 'xls':
                return self::importExcelWithColumns($file['tmp_name'], $extension);

            default:
                throw new \Exception('Format de fichier non supporté: ' . $extension);
        }
    }

    /**
     * Import CSV with full column detection
     *
     * @param string $filePath Path to CSV file
     * @return array ['headers' => [...], 'data' => [[...], [...]]]
     */
    private static function importCSVWithColumns(string $filePath): array
    {
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \Exception('Impossible d\'ouvrir le fichier CSV');
        }

        $headers = [];
        $data = [];
        $isFirstRow = true;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if ($isFirstRow) {
                $isFirstRow = false;

                // Check if first row is header
                if (!empty($row[0]) && !self::looksLikePhoneNumber($row[0])) {
                    // First row is header
                    $headers = array_map('trim', $row);
                    continue;
                } else {
                    // First row is data, generate generic headers
                    for ($i = 0; $i < count($row); $i++) {
                        $headers[] = 'Colonne' . ($i + 1);
                    }
                }
            }

            // Store row data
            if (!empty($row[0])) {
                $rowData = [];
                foreach ($row as $index => $value) {
                    $header = $headers[$index] ?? 'Colonne' . ($index + 1);
                    $rowData[$header] = trim($value);
                }
                $data[] = $rowData;
            }
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'data' => $data
        ];
    }

    /**
     * Import Excel with full column detection
     *
     * @param string $filePath Path to Excel file
     * @param string $extension File extension (xlsx or xls)
     * @return array ['headers' => [...], 'data' => [[...], [...]]]
     */
    private static function importExcelWithColumns(string $filePath, string $extension): array
    {
        // Check if PhpSpreadsheet is available
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            // Fallback: treat as CSV
            return self::importCSVWithColumns($filePath);
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $headers = [];
            $data = [];
            $isFirstRow = true;

            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = trim($cell->getValue());
                }

                // Skip empty rows
                if (empty($rowData[0])) {
                    continue;
                }

                if ($isFirstRow) {
                    $isFirstRow = false;

                    // Check if first row is header
                    if (!self::looksLikePhoneNumber($rowData[0])) {
                        // First row is header
                        $headers = $rowData;
                        continue;
                    } else {
                        // First row is data, generate generic headers
                        for ($i = 0; $i < count($rowData); $i++) {
                            $headers[] = 'Colonne' . ($i + 1);
                        }
                    }
                }

                // Store row data as associative array
                $assocData = [];
                foreach ($rowData as $index => $value) {
                    $header = $headers[$index] ?? 'Colonne' . ($i + 1);
                    $assocData[$header] = $value;
                }
                $data[] = $assocData;
            }

            return [
                'headers' => $headers,
                'data' => $data
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la lecture du fichier Excel: ' . $e->getMessage());
        }
    }

    /**
     * Replace variables in message template with actual data
     * Variables format: {{column_name}}
     *
     * @param string $template Message template
     * @param array $data Row data as associative array
     * @return string Message with variables replaced
     */
    public static function replaceVariables(string $template, array $data): string
    {
        $message = $template;

        // Replace each variable
        foreach ($data as $key => $value) {
            // Support both {{key}} and {{KEY}} formats
            $message = str_replace('{{' . $key . '}}', $value, $message);
            $message = str_replace('{{' . strtolower($key) . '}}', $value, $message);
            $message = str_replace('{{' . strtoupper($key) . '}}', $value, $message);
        }

        return $message;
    }

    /**
     * Generate a CSV template file
     *
     * @return string CSV content
     */
    public static function generateTemplate(): string
    {
        return "Téléphone,Nom,Prenom,Montant\n" .
            "0708090102,Dupont,Jean,1000\n" .
            "0709101112,Martin,Marie,2500\n" .
            "+221771234567,Diallo,Amadou,1500\n" .
            "+33612345678,Lefebvre,Pierre,3000\n";
    }
}
