<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Application\Validator;

use EvolutionCMS\FeatureFlags\Application\Exceptions\FlagValidationException;

final class FlagValidator
{
    /**
     * Валидация данных для создания флага
     */
    public function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['name']) || !is_string($data['name'])) {
            $errors['name'][] = 'Имя флага обязательно.';
        } elseif (!preg_match('/^[a-z0-9_]+$/', $data['name'])) {
            $errors['name'][] = 'Имя должно быть в snake_case (латиница, цифры, _).';
        } elseif (strlen($data['name']) > 100) {
            $errors['name'][] = 'Имя не должно превышать 100 символов.';
        }

        if (isset($data['description']) && strlen((string)$data['description']) > 500) {
            $errors['description'][] = 'Описание не должно превышать 500 символов.';
        }

        if (!empty($errors)) {
            throw new FlagValidationException($errors);
        }
    }

    /**
     * Валидация данных для обновления флага
     */
    public function validateUpdate(array $data): void
    {
        $errors = [];

        if (isset($data['description']) && strlen((string)$data['description']) > 500) {
            $errors['description'][] = 'Описание не должно превышать 500 символов.';
        }

        if (!empty($errors)) {
            throw new FlagValidationException($errors);
        }
    }
}
