<?php

class SiteSettings {
    private $pdo;
    private static $cachedSettings = null;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Fetch all site settings
     
    public function getAll(): array {
        if (self::$cachedSettings !== null) {
            return self::$cachedSettings;
        }

        $stmt = $this->pdo->query("SELECT * FROM site_settings WHERE id = 1 LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        self::$cachedSettings = $settings ?: [];
        return self::$cachedSettings;
    }

    // Update site settings
     
    public function update(array $data): void {
        $fields = [
            'site_description', 'rules', 'contact_info', 'primary_color', 'font_family',
            'about_title1','about_subtitle1','about_text1',
            'about_title2','about_subtitle2','about_text2',
            'how_title','how_subtitle',
            'card1_title','card1_text','card2_title','card2_text','card3_title','card3_text'
        ];

        $setParts = [];
        $values = [];
        foreach ($fields as $f) {
            if (isset($data[$f])) {
                $setParts[] = "$f = ?";
                $values[] = $data[$f];
            }
        }

        if (!empty($setParts)) {
            $sql = "UPDATE site_settings SET " . implode(",", $setParts) . " WHERE id = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);

            // Clear cache after update
            self::$cachedSettings = null;
        }
    }
}
?>
