<?php

namespace Oscar\Service\administration;

class CheckConfigItem
{
    private string $name;

    private string $description;

    private string $tech_name;

    private string $tech_info;

    private string $value;

    private string $status;

    private array $messages;

    const STATUS_ERROR = 'error';
    const STATUS_WARNING = 'warning';
    const STATUS_SUCCESS = 'success';

    /**
     * @param string $name
     * @param string $description
     * @param string $tech_name
     */
    public function __construct(string $name, string $description = "", string $tech_name = "")
    {
        $this->name = $name;
        $this->description = $description;
        $this->tech_name = $tech_name;
        $this->messages = [];
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTechName(): string
    {
        return $this->tech_name;
    }

    public function setTechName(string $tech_name): self
    {
        $this->tech_name = $tech_name;
        return $this;
    }

    public function getTechInfo(): string
    {
        return $this->tech_info;
    }

    public function setTechInfo(string $tech_info): self
    {
        $this->tech_info = $tech_info;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getMessages(): string
    {
        return $this->messages;
    }

    public function setMessages(string $messages): self
    {
        $this->messages = $messages;
        return $this;
    }

    public function addError(string $string)
    {
        $this->messages[] = $string;
    }


}