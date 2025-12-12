<?php
use App\Helpers\CommonHelper;
?>

@if (count($rating) === 0)
Нет примеров для разбора
@endif

@if(count($rating) > 0)
📌 {{ $rating[0] }}

@if ($rating[4] === 'Для разбора')
‼️ {{ $rating[4] }}
@elseif ($rating[4] === 'Возражение')
‼️ {{ $rating[4] }}
@elseif ($rating[4] === 'ТОП')
✅ {{ $rating[4] }}
@elseif ($rating[4] === 'ХОРОШО')
✅ {{ $rating[4] }}
@else
❌ {{ $rating[4] }}
@endif

📞 {{ $rating[3] }}

📝 Сделка: {{ $rating[2] }}

@if ($rating[4] === 'ТОП') 🥇 @else ✍️ @endif {{ $rating[1] }}
@endif
