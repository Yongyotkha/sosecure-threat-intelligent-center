<?php

namespace App\Helpers;

class HtmlCleaner
{
    /**
     * ย่อ "บรรทัดแรก" ของย่อหน้าที่เข้าหลักเกณฑ์
     * - ไม่ยุ่งกับ line-height (ใช้ zero-height spacer)
     * - ข้าม: .noindent, จัดกลาง, บูลเล็ต, ย่อหน้าสั้น ๆ, ย่อหน้าที่เป็นรูป
     */
    public static function smartIndentParagraphs(
        string $html,
        int $threshold = 2,          // ใช้เฉพาะโหมด auto
        string $indent = '0',        // ไม่ใช้ text-indent แล้ว (กันซ้ำ) ตั้งเป็น '0'
        string $mode = 'always',     // 'always' แต่อยู่ภายใต้ minChars
        int $fallbackNbspCount = 0,  // ปิด NBSP เพื่อไม่ให้รบกวน spacing
        ?string $hardIndentWidth = null, // ไม่ใช้ตัวคั่นเดิมแบบมีความสูง
        int $minChars = 28,          // ยาวขั้นต่ำที่ "จะย่อ" เพื่อไม่ไปย่อหัวข้อ
        string $spacerWidth = '2.8em'// ความกว้างที่ต้องการย่อจริง ๆ (zero-height spacer)
    ): string {
        // ล้าง XML junk
        $html = preg_replace('~<\?xml[^>]*\?>~i', '', $html);
        $html = preg_replace('~<\?xml[^>]*>~i', '', $html);
        $html = preg_replace('~<!--\?xml[^>]*\?-->~i', '', $html);

        return preg_replace_callback('~<(p|div)\b([^>]*)>(.*?)</\1>~is', function ($m) use ($threshold, $mode, $minChars, $spacerWidth) {
            $tag   = strtolower($m[1]);
            $attrs = $m[2] ?? '';
            $inner = $m[3] ?? '';

            // ข้ามกรณีไม่ต้องย่อ
            if (preg_match('~\bclass="[^"]*\bnoindent\b~i', $attrs)) return "<{$tag}{$attrs}>{$inner}</{$tag}>";
            if (preg_match('~align\s*=\s*"center"~i', $attrs) || preg_match('~text-align\s*:\s*center~i', $attrs.$inner)) {
                return "<{$tag}{$attrs}>{$inner}</{$tag}>";
            }
            $inner_no_ws = trim(strip_tags($inner, '<img>'));
            if ($inner_no_ws !== '' && preg_match('~^<(?:img)\b~i', ltrim($inner_no_ws))) {
                return "<{$tag}{$attrs}>{$inner}</{$tag}>";
            }
            $probe = preg_replace('~^(?:&nbsp;|&#160;|\xC2\xA0|[\x{2000}-\x{200B}]|\s)+~u', '', strip_tags($inner));
            if (preg_match('~^(?:-|•|–|\x{2022})\s~u', $probe)) {
                return "<{$tag}{$attrs}>{$inner}</{$tag}>";
            }
            // ย่อเฉพาะย่อหน้าที่ยาวพอ
            $plain = preg_replace('~\s+~u', ' ', trim(strip_tags($inner)));
            if (mb_strlen($plain) < $minChars) {
                return "<{$tag}{$attrs}>{$inner}</{$tag}>";
            }

            // โหมด auto: ต้องมีช่องว่างนำหน้าอย่างน้อย threshold
            if ($mode !== 'always') {
                $hasLeadingPad = preg_match(
                    '~^((?:<(?:span|font|b|i|u|strong|em|sub|sup|a)\b[^>]*>)*)' .
                    '(?:[\x{00A0}\x{2000}-\x{200B}\s]{' . $threshold . ',})~u',
                    $inner
                );
                if (!$hasLeadingPad) return "<{$tag}{$attrs}>{$inner}</{$tag}>";
            }

            // ตัดช่องว่างนำหน้า (จะไปใช้ spacer แทน)
            $inner = preg_replace(
                '~^((?:<(?:span|font|b|i|u|strong|em|sub|sup|a)\b[^>]*>)*)' .
                '(?:[\x{00A0}\x{2000}-\x{200B}\s]+)~u',
                '$1',
                $inner
            );

            // ใส่ zero-height spacer แค่ครั้งเดียว
            if (!preg_match('~^\s*<span\b[^>]*\bindent-sp0\b~i', $inner)) {
                $spacerStyle = 'display:inline-block;width:' . htmlspecialchars($spacerWidth, ENT_QUOTES)
                             . ';max-width:' . htmlspecialchars($spacerWidth, ENT_QUOTES)
                             . ';font-size:0;line-height:0;vertical-align:baseline;';
                $spacer = '<span class="indent-sp0" style="' . $spacerStyle . '">&#8203;</span>';
                $inner = $spacer . $inner;
            }

            // ลบ text-indent เดิม และล็อกให้เป็น 0 เพื่อไม่ซ้ำกับ spacer
            if (preg_match('~\bstyle=(["\'])(.*?)\1~i', $attrs, $mm)) {
                $style = preg_replace('~text-indent\s*:\s*[^;]+;?~i', '', $mm[2]);
                $style = rtrim($style, " ;\t\n\r\0\x0B");
                $style = $style ? ($style . '; text-indent:0 !important;') : 'text-indent:0 !important;';
                $attrs = preg_replace('~\bstyle=(["\'])(.*?)\1~i', 'style="' . $style . '"', $attrs);
            } else {
                $attrs .= ' style="text-indent:0 !important;"';
            }

            return "<{$tag}{$attrs}>{$inner}</{$tag}>";
        }, $html);
    }

    public static function safeEmailHtml(string $html): string
    {
        $html = preg_replace('~<\?xml[^>]*\?>~i', '', (string)$html);
        $html = preg_replace('~<\?xml[^>]*>~i', '', (string)$html);
        $html = preg_replace('~<!--\?xml[^>]*\?-->~i', '', (string)$html);
        return $html;
    }
    
}
