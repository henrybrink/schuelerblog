<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SettingRepository")
 */
class Setting {

    public static function create($name, $type, $defaultValue) {
        $setting = new Setting();
        $setting->setName($name);
        $setting->setValue($defaultValue);
        $setting->setDefaultValue($defaultValue);
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
}
