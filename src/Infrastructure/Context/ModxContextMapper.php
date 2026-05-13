<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Infrastructure\Context;

use FeatureFlags\Core\Domain\ValueObject\EvaluationContext;

/**
 * Инфраструктурный маппер: преобразует "сырые" данные Evolution CMS
 * в массив, совместимый с EvaluationContext::fromArray()
 *
 * Не возвращает EvaluationContext — это делает ядро внутри isEnabled()
 */
final readonly class ModxContextMapper
{
    public function __construct(private array $rawModxData = [])
    {
    }

    /**
     * @return array Массив, готовый для передачи в FeatureFlagService::isEnabled()
     */
    public function build(): array
    {
        $context = [];

        // 1. Маппинг документа -> target_id (ожидается TargetIdSpecification)
        if (isset($this->rawModxData['documentIdentifier'])) {
            $context['target_id'] = (string)$this->rawModxData['documentIdentifier'];
        }

        // 2. Роль пользователя
        if (isset($this->rawModxData['user_role'])) {
            $context['user_role'] = $this->rawModxData['user_role'];
        }

        // 3. Категория (пробрасываем как есть для CategorySpecification)
        if (isset($this->rawModxData['category'])) {
            $context['category'] = $this->rawModxData['category'];
        }

        // 4. Текущая дата (авто-подстановка, если не передана)
        $context['current_date'] = $this->rawModxData['current_date'] ?? date('Y-m-d');

        // 5. Окружение (маппинг из site_status Evo)
        if (isset($this->rawModxData['environment'])) {
            $context['environment'] = $this->rawModxData['environment'];
        } else {
            $status = $this->rawModxData['site_status'] ?? '1';
            $context['environment'] = ($status == '0') ? 'maintenance' : 'production';
        }

        // 6. User Hash для A/B (авто-генерация, если не передан)
        if (isset($this->rawModxData['user_hash'])) {
            $context['user_hash'] = $this->rawModxData['user_hash'];
        } else {
            $ip = $this->rawModxData['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            $ua = $this->rawModxData['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');
            $context['user_hash'] = md5($ip . $ua);
        }

        // 7. Пробрасываем кастомные ключи без изменений
        $skipKeys = ['documentIdentifier', 'site_status', 'ip', 'user_agent'];
        foreach ($this->rawModxData as $key => $value) {
            if (!in_array($key, $skipKeys, true) && !isset($context[$key])) {
                $context[$key] = $value;
            }
        }

        return $context;
    }
}
