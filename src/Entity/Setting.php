<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SettingRepository")
 */
class Setting {

    public static function create($name, $displayName, $description,  $type, $defaultValue) {
        $setting = new Setting();
        $setting->setName($name);
        $setting->setValue($defaultValue);
        $setting->setDefaultValue($defaultValue);
        $setting->setDisplayName($displayName);
        $setting->setDescription($description);
        $setting->setType($type);
        return $setting;
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\Column(type="json")
     */
    private $value = [];

    /**
     * @ORM\Column(type="json")
     */
    private $defaultValue = [];

    /**
     * @ORM\Column(type="text")
     */
    private $type;

    /**
     * @ORM\Column(type="text")
     */
    private $displayName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?array
    {
        return $this->value;
    }

    public function setValue(array $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getDefaultValue(): ?array
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(array $defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array[Setting] - All the defaults settings
     */
    public static function defaultSettings() : array {
        $settings = [];

        $settings["version"] = self::create("settings.version", "", "", "hidden", ['currentVersion' => 0.2]);
        $settings[] = self::create("footer.text", "Textbereich Fußzeile", "Dieser Text wird in der Textzeile des Footers im Frontend dargestellt", "text", ["text" => "Bitte diesen Text ersetzen!"]);
        $settings[] = self::create("index.description", "Beschreibung auf der Startseite", "Auf der Startseite befindet sich ein weiterer Textbereich.", "text", ["text" => "Bitte diesen Text ersetzen!"]);

        return $settings;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
