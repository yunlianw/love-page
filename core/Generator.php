<?php
/**
 * 恋爱页面生成器
 */
class LoveGenerator {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    private function getData(): array {
        $config = $this->pdo->query("SELECT * FROM love_config LIMIT 1")->fetch();
        $travel = $this->pdo->query("SELECT * FROM love_travel ORDER BY sort_order, id")->fetchAll();
        $gallery = $this->pdo->query("SELECT * FROM love_gallery ORDER BY sort_order, id")->fetchAll();
        $hobbies = $this->pdo->query("SELECT * FROM love_hobbies ORDER BY type, sort_order, id")->fetchAll();
        $together = $this->pdo->query("SELECT * FROM love_together ORDER BY sort_order, id")->fetchAll();
        $countdown = $this->pdo->query("SELECT * FROM love_countdown WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();
        $locations = $this->pdo->query("SELECT * FROM love_location WHERE is_show=1 ORDER BY sort_order, id")->fetchAll();
        $music = $this->pdo->query("SELECT * FROM love_music WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();

        $hobbyGrouped = ['left' => [], 'right' => [], 'shared' => []];
        foreach ($hobbies as $h) {
            $hobbyGrouped[$h['type']][] = $h;
        }

        return [
            'config' => $config,
            'travel' => $travel,
            'gallery' => $gallery,
            'hobbies' => $hobbyGrouped,
            'together' => $together,
            'countdown' => $countdown,
            'locations' => $locations,
            'music' => $music,
            'site_url' => SITE_URL
        ];
    }

    private function h(?string $s): string {
        return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    }

    public function generate(): array {
        $data = $this->getData();
        $c = $data['config'];

        // 头像
        $avatarLeft = $c['avatar_left'] ?? '';
        $avatarLeftHTML = $avatarLeft
            ? '<img src="'.$this->h($avatarLeft).'" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" alt="TA">'
            : $this->h($c['emoji_left']);
        $avatarRight = $c['avatar_right'] ?? '';
        $avatarRightHTML = $avatarRight
            ? '<img src="'.$this->h($avatarRight).'" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" alt="TA">'
            : $this->h($c['emoji_right']);

        // 旅行
        $travelHTML = '';
        if ($c['show_travel'] ?? 1) {
            foreach ($data['travel'] as $i => $t) {
                $odd = $i % 2 === 0;
                $margin = $odd ? 'margin-right:50px;text-align:right;' : 'margin-left:50px;';
                $imgUrl = $t['image_url'] ?? '';
                $imgHtml = $imgUrl
                    ? '<img src="'.$this->h($imgUrl).'" style="width:100%;height:150px;object-fit:cover;border-radius:10px;margin-bottom:10px;">'
                    : '<div style="width:100%;height:150px;background:linear-gradient(135deg,#ffe0e8,#fff0f3);border-radius:10px;margin-bottom:10px;display:flex;align-items:center;justify-content:center;font-size:50px;">'.$this->h($t['emoji']).'</div>';
                $travelHTML .= '<div class="timeline-item"><div class="tl-content" style="'.$margin.'">'.$imgHtml.'<div class="tl-date">'.$this->h($t['travel_date']).'</div><div class="tl-place">'.$this->h($t['place']).'</div><div class="tl-desc">'.$this->h($t['description']).'</div></div><div class="tl-dot"></div></div>';
            }
        }

        // 相册
        $galleryHTML = '';
        if ($c['show_gallery'] ?? 1) {
            foreach ($data['gallery'] as $i => $g) {
                $spanClass = ($i + 1) % 3 === 1 ? ' grid-row:span 2;aspect-ratio:auto;' : '';
                $imgUrl = $g['image_url'] ?? '';
                $inner = $imgUrl
                    ? '<img src="'.$this->h($imgUrl).'" style="width:100%;height:100%;object-fit:cover;">'
                    : '<div class="placeholder">'.$this->h($g['emoji']).'<span>'.$this->h($g['title']).'</span></div>';
                $galleryHTML .= '<div class="gallery-item" style="'.$spanClass.'">'.$inner.'</div>';
            }
        }

        // 爱好
        $hobbyHTML = '';
        if ($c['show_hobbies'] ?? 1) {
            $leftLabel = $avatarLeft ? 'TA的爱好' : $this->h($c['emoji_left']).' TA的爱好';
            $rightLabel = $avatarRight ? 'TA的爱好' : $this->h($c['emoji_right']).' TA的爱好';
            $typeLabels = ['left' => $leftLabel, 'right' => $rightLabel, 'shared' => '💕 我们的共同爱好'];
            foreach ($data['hobbies'] as $type => $list) {
                $label = $typeLabels[$type];
                $badge = $type === 'shared' ? '<span class="shared-badge">更多</span>' : '';
                $borderStyle = $type === 'shared' ? 'border-color:var(--pink-light);border-width:2px;' : '';
                $tags = '';
                foreach ($list as $h) {
                    $tags .= '<span class="hobby-tag '.$this->h($h['color']).'">'.$this->h($h['content']).'</span>';
                }
                $hobbyHTML .= '<div class="hobby-card" style="'.$borderStyle.'"><h3>'.$label.$badge.'</h3>'.$tags.'</div>';
            }
        }

        // 一起做的事
        $togetherHTML = '';
        if ($c['show_together'] ?? 1) {
            foreach ($data['together'] as $t) {
                $countBadge = $t['count_label'] ? '<div class="together-count">'.$this->h($t['count_label']).'</div>' : '';
                $togetherHTML .= '<div class="together-card"><div class="together-icon">'.$this->h($t['emoji']).'</div><h3>'.$this->h($t['title']).'</h3><p>'.$this->h($t['description']).'</p>'.$countBadge.'</div>';
            }
        }

        // 纪念日倒计时（生成静态HTML时计算快照，运行时JS实时刷新）
        $countdownHTML = '';
        if ($c['show_countdown'] ?? 1) {
            $colorClass = ['pink'=>'bg-pink','purple'=>'bg-purple','gold'=>'bg-gold','blue'=>'bg-blue'];
            foreach ($data['countdown'] as $cd) {
                $bgClass = $colorClass[$cd['bg_color']] ?? 'bg-pink';
                // 快照（JS禁用时的fallback），运行时由JS按 data-target 实时刷新
                $target = new DateTime($cd['target_date']);
                $now = new DateTime();
                $diff = $now->diff($target);
                $days = $target > $now ? $diff->days : -$diff->days;
                $daysText = $days > 0 ? "还有 {$days} 天" : ($days == 0 ? "就是今天！" : "已过 ".abs($days)." 天");
                $countdownHTML .= '<div class="countdown-card '.$bgClass.'"><div class="cd-emoji">'.$this->h($cd['emoji']).'</div><h3>'.$this->h($cd['name']).'</h3><p>'.$this->h($cd['description']).'</p><div class="cd-date">'.$this->h($cd['target_date']).'</div><div class="cd-days" id="cd-days-'.intval($cd['id']).'" data-target="'.$this->h($cd['target_date']).'">'.$daysText.'</div></div>';
            }
        }

        // 地图
        $mapHTML = '';
        if ($c['show_location'] ?? 1) {
            foreach ($data['locations'] as $loc) {
                $mapUrl = $loc['map_type'] === 'gaode'
                    ? 'https://uri.amap.com/marker?position='.$loc['lng'].','.$loc['lat'].'&name='.urlencode($loc['name'])
                    : 'https://api.map.baidu.com/marker?location='.$loc['lat'].','.$loc['lng'].'&title='.urlencode($loc['name']).'&output=html';
                $mapHTML .= '<div class="map-card"><h3>📍 '.$this->h($loc['name']).'</h3><p>'.$this->h($loc['address']).'</p><a href="'.$mapUrl.'" target="_blank" class="map-link">查看地图导航</a></div>';
            }
        }

        // 音乐播放器数据
        $musicData = [];
        if ($c["show_music_player"] ?? 1) {
        $musicData = [];
        foreach ($data['music'] as $m) {
            $musicData[] = [
                'title' => $m['title'],
                'artist' => $m['artist'] ?? '',
                'url' => $m['audio_url'],
                'cover' => $m['cover_url'] ?? ''
            ];
        }
        }
        $musicJSON = json_encode($musicData, JSON_UNESCAPED_UNICODE);
        $showPlayer = ($c['show_music_player'] ?? 1) && count($musicData) > 0;
        $autoplay = $c['music_autoplay'] ?? 1;
        $hideHours = intval($c['music_hide_hours'] ?? 24);
        // 构建HTML
        $sections = '';

        // 纪念日（Hero下方）
        if ($countdownHTML) {
            $sections .= '<section class="section countdown-section"><div class="section-title"><h2>📅 纪念日倒计时</h2><p>期待每一个重要时刻</p></div><div class="container"><div class="countdown-grid">'.$countdownHTML.'</div></div></section>';
        }

        // 旅行
        if ($travelHTML) {
            $sections .= '<section class="section travel-section"><div class="section-title"><h2>✈️ 旅行足迹</h2><p>一起去过的地方，都是最美的风景</p></div><div class="container"><div class="timeline">'.$travelHTML.'</div></div></section>';
        }

        // 相册
        if ($galleryHTML) {
            $sections .= '<section class="section gallery-section"><div class="section-title"><h2>📸 甜蜜相册</h2><p>每一张照片，都是一个小故事</p></div><div class="container"><div class="gallery-grid">'.$galleryHTML.'</div></div></section>';
        }

        // 爱好
        if ($hobbyHTML) {
            $sections .= '<section class="section hobbies-section"><div class="section-title"><h2>🎨 爱好与共同爱好</h2><p>你的我的，都是我们的</p></div><div class="container"><div class="hobbies-grid">'.$hobbyHTML.'</div></div></section>';
        }

        // 一起做的事
        if ($togetherHTML) {
            $sections .= '<section class="section together-section"><div class="section-title"><h2>💝 一起做的事</h2><p>这些小事，构成了我们的每一天</p></div><div class="container"><div class="together-grid">'.$togetherHTML.'</div></div></section>';
        }

        // 地图
        if ($mapHTML) {
            $sections .= '<section class="section map-section"><div class="section-title"><h2>📍 地点导航</h2><p>我们在哪里等你们</p></div><div class="container" style="max-width:800px">'.$mapHTML.'</div></section>';
        }

        $html = $this->buildHTML($c, $avatarLeftHTML, $avatarRightHTML, $sections, $musicJSON, $showPlayer, $autoplay, $hideHours);
        file_put_contents(ROOT_PATH . '/index.html', $html);

        return [
            'config' => $c,
            'travel_count' => count($data['travel']),
            'gallery_count' => count($data['gallery']),
            'hobbies_count' => array_sum(array_map('count', $data['hobbies'])),
            'together_count' => count($data['together']),
            'countdown_count' => count($data['countdown']),
            'location_count' => count($data['locations']),
            'file_size' => strlen($html)
        ];
    }

    private function buildHTML(array $c, string $avatarLeftHTML, string $avatarRightHTML, string $sections, string $musicJSON, bool $showPlayer, bool $autoplay, int $hideHours): string {
        $dt = substr($c["default_date"] ?? "2024-01-01", 0, 16);
        $dateOnly = substr($c["default_date"] ?? "2024-01-01", 0, 10);
        $pageLock = $c['page_lock'] ?? 0;
        $lockHash = '';
        if ($pageLock && !empty($c['page_password'])) {
            // FNV-1a hash
            $pw = $c['page_password'];
            $h = 0x811c9dc5;
            for ($i = 0; $i < strlen($pw); $i++) {
                $h ^= ord($pw[$i]);
                $h = ($h * 0x01000193) & 0xFFFFFFFF;
            }
            $lockHash = sprintf('%08x', $h);
        }
        
        return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$this->h($c['title'])}</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=ZCOOL+KuaiLe&family=Noto+Sans+SC:wght@300;400;500;700&display=swap');
*{margin:0;padding:0;box-sizing:border-box}
:root{--pink:#ff6b8a;--pink-light:#ffb3c6;--pink-dark:#e84573;--warm:#fff5f7;--warm2:#fff0f3;--purple:#c77dba;--gold:#f4c87d;--text:#4a3347;--text-light:#8b6b7d;--shadow:0 4px 20px rgba(255,107,138,0.15);--tag1-bg:#ffe0e8;--tag1-color:#e84573;--tag2-bg:#f3e5f5;--tag2-color:#8e24aa;--tag3-bg:#fff8e1;--tag3-color:#f57f17;--tag4-bg:#e8f5e9;--tag4-color:#2e7d32;--tag5-bg:#e3f2fd;--tag5-color:#1565c0;--hero-grad:linear-gradient(135deg,#fff5f7 0%,#ffe0e8 50%,#f5e6ff 100%);--hero-bg1:rgba(255,107,138,0.15);--hero-bg2:rgba(199,125,186,0.12);--timeline-grad:linear-gradient(180deg,var(--pink-light),var(--purple));--shared-grad:linear-gradient(135deg,var(--pink),var(--purple))}
[data-theme="blue"]{--pink:#5b8def;--pink-light:#a8c4ff;--pink-dark:#3a6bdb;--warm:#f0f4ff;--warm2:#e8eeff;--purple:#7c8cf5;--gold:#89c4f4;--text:#2c3e5a;--text-light:#6b82a6;--shadow:0 4px 20px rgba(91,141,239,0.15);--tag1-bg:#e3f2fd;--tag1-color:#1565c0;--tag2-bg:#e8eaf6;--tag2-color:#3949ab;--tag3-bg:#e0f7fa;--tag3-color:#00838f;--tag4-bg:#e8f5e9;--tag4-color:#2e7d32;--tag5-bg:#fff3e0;--tag5-color:#e65100;--hero-grad:linear-gradient(135deg,#f0f4ff 0%,#dbeafe 50%,#e0e7ff 100%);--hero-bg1:rgba(91,141,239,0.15);--hero-bg2:rgba(124,140,245,0.12);--shared-grad:linear-gradient(135deg,var(--pink),var(--purple))}
.theme-switcher{position:fixed;top:20px;right:20px;z-index:100;display:flex;gap:8px;background:rgba(255,255,255,0.9);backdrop-filter:blur(10px);padding:8px 14px;border-radius:25px;box-shadow:var(--shadow)}
.theme-btn{width:28px;height:28px;border-radius:50%;border:2px solid #fff;cursor:pointer;transition:transform .2s,box-shadow .2s;box-shadow:0 2px 6px rgba(0,0,0,0.15)}
.theme-btn:hover{transform:scale(1.15)}.theme-btn.active{transform:scale(1.2);box-shadow:0 0 0 3px rgba(0,0,0,0.1)}
.theme-btn.pink{background:linear-gradient(135deg,#ff6b8a,#c77dba)}.theme-btn.blue{background:linear-gradient(135deg,#5b8def,#7c8cf5)}
body{font-family:'Noto Sans SC',sans-serif;background:var(--warm);color:var(--text);overflow-x:hidden}
.hearts-container{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;overflow:hidden}
.floating-heart{position:absolute;top:-5%;animation:floatDown linear infinite;opacity:.3}
@keyframes floatDown{0%{transform:translateY(0) rotate(0deg) scale(1);opacity:0}10%{opacity:.3}90%{opacity:.3}100%{transform:translateY(110vh) rotate(360deg) scale(.5);opacity:0}}
.hero{min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;position:relative;background:var(--hero-grad);padding:20px}
.hero::before{content:'';position:absolute;width:300px;height:300px;background:radial-gradient(circle,var(--hero-bg1),transparent 70%);top:10%;left:10%;border-radius:50%;animation:pulse 4s ease-in-out infinite}
.hero::after{content:'';position:absolute;width:250px;height:250px;background:radial-gradient(circle,var(--hero-bg2),transparent 70%);bottom:10%;right:10%;border-radius:50%;animation:pulse 5s ease-in-out infinite reverse}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.2)}}
.hero-avatar{display:flex;align-items:center;gap:20px;margin-bottom:30px;position:relative;z-index:1}
.avatar{width:100px;height:100px;border-radius:50%;border:4px solid var(--pink-light);box-shadow:var(--shadow);background:var(--pink-light);display:flex;align-items:center;justify-content:center;font-size:40px}
.avatar-left{animation:bounce 3s ease-in-out infinite}.avatar-right{animation:bounce 3s ease-in-out infinite 1.5s}
@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
.heart-link{font-size:36px;animation:heartbeat 1.5s ease-in-out infinite}
@keyframes heartbeat{0%,100%{transform:scale(1)}15%{transform:scale(1.3)}30%{transform:scale(1)}45%{transform:scale(1.15)}}
.hero h1{font-family:'ZCOOL KuaiLe',cursive;font-size:2.8rem;color:var(--pink);margin-bottom:10px;position:relative;z-index:1}
.hero .subtitle{font-size:1.1rem;color:var(--text-light);margin-bottom:30px;position:relative;z-index:1}
.love-timer{background:rgba(255,255,255,0.8);backdrop-filter:blur(10px);border-radius:20px;padding:25px 40px;box-shadow:var(--shadow);position:relative;z-index:1;border:2px solid rgba(255,179,198,0.3)}
.love-timer .label{font-size:.9rem;color:var(--text-light);margin-bottom:10px}
.love-timer .date-config input{border:2px solid var(--pink-light);border-radius:10px;padding:8px 16px;font-size:1rem;font-family:inherit;color:var(--text);outline:none;text-align:center;margin-bottom:15px}
.timer-grid{display:flex;gap:15px;justify-content:center;flex-wrap:wrap}
.timer-item{text-align:center;min-width:70px}
.timer-item .num{font-family:'ZCOOL KuaiLe',cursive;font-size:2.5rem;color:var(--pink);line-height:1}
.timer-item .unit{font-size:.8rem;color:var(--text-light);margin-top:4px}
.scroll-hint{position:absolute;bottom:30px;left:50%;transform:translateX(-50%);animation:scrollBounce 2s infinite;z-index:1;color:var(--pink-light);font-size:1.5rem}
@keyframes scrollBounce{0%,100%{transform:translateX(-50%) translateY(0);opacity:1}50%{transform:translateX(-50%) translateY(10px);opacity:.5}}
.section{padding:80px 20px;position:relative;z-index:1;overflow:hidden}
.section-title{text-align:center;margin-bottom:50px}
.section-title h2{font-family:'ZCOOL KuaiLe',cursive;font-size:2rem;color:var(--pink);display:inline-flex;align-items:center;gap:10px}
.section-title p{color:var(--text-light);margin-top:8px;font-size:.95rem}
.container{max-width:1100px;margin:0 auto}
.countdown-section{background:linear-gradient(180deg,var(--warm) 0%,#fce4ec 100%)}
.countdown-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;max-width:800px;margin:0 auto}
.countdown-card{background:rgba(255,255,255,0.9);backdrop-filter:blur(10px);border-radius:20px;padding:25px;text-align:center;box-shadow:var(--shadow);border:1px solid rgba(255,179,198,0.2);transition:transform .3s}
.countdown-card:hover{transform:translateY(-5px)}
.countdown-card.bg-pink{background:linear-gradient(135deg,#fff5f7,#ffe0e8)}
.countdown-card.bg-purple{background:linear-gradient(135deg,#f9f0ff,#e1bee7)}
.countdown-card.bg-gold{background:linear-gradient(135deg,#fffde7,#f4c87d)}
.countdown-card.bg-blue{background:linear-gradient(135deg,#f0f4ff,#bbdefb)}
.cd-emoji{font-size:2.5rem;margin-bottom:10px}
.countdown-card h3{font-family:'ZCOOL KuaiLe',cursive;font-size:1.2rem;color:var(--text);margin-bottom:6px}
.countdown-card p{font-size:.85rem;color:var(--text-light);margin-bottom:8px}
.cd-date{font-size:.8rem;color:var(--text-light);margin-bottom:10px}
.cd-days{font-family:'ZCOOL KuaiLe',cursive;font-size:1.6rem;color:var(--pink);background:rgba(255,255,255,0.6);display:inline-block;padding:6px 20px;border-radius:20px}
.travel-section{background:linear-gradient(180deg,var(--warm) 0%,#fce4ec 100%)}
.timeline{position:relative;max-width:800px;margin:0 auto;overflow:visible}
.timeline::before{content:'';position:absolute;left:50%;top:0;bottom:0;width:3px;background:var(--timeline-grad);transform:translateX(-50%);border-radius:3px}
.timeline-item{display:flex;margin-bottom:40px;position:relative}
.tl-content{flex:1;background:rgba(255,255,255,0.9);backdrop-filter:blur(10px);border-radius:16px;padding:20px;box-shadow:var(--shadow);border:1px solid rgba(255,179,198,0.2);transition:transform .3s,box-shadow .3s}
.tl-content:hover{transform:translateY(-3px);box-shadow:0 8px 30px rgba(255,107,138,0.2)}
.tl-date{font-size:.8rem;color:var(--pink);font-weight:500;margin-bottom:6px}
.tl-place{font-family:'ZCOOL KuaiLe',cursive;font-size:1.2rem;color:var(--text);margin-bottom:6px}
.tl-desc{font-size:.9rem;color:var(--text-light);line-height:1.6}
.tl-dot{position:absolute;left:50%;top:20px;width:14px;height:14px;background:var(--pink);border:3px solid #fff;border-radius:50%;transform:translateX(-50%);box-shadow:0 0 0 3px var(--pink-light)}
.gallery-section{background:#fce4ec}
.gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.gallery-item{border-radius:16px;overflow:hidden;position:relative;aspect-ratio:1;background:linear-gradient(135deg,var(--pink-light),var(--warm2));box-shadow:var(--shadow);cursor:pointer;transition:transform .3s}
.gallery-item:hover{transform:scale(1.03)}
.gallery-item .placeholder{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--pink);font-size:50px;gap:8px}
.gallery-item .placeholder span{font-size:.85rem;color:var(--text-light)}
.gallery-item img{width:100%;height:100%;object-fit:cover}
.hobbies-section{background:linear-gradient(180deg,#fce4ec 0%,#f3e5f5 100%)}
.hobbies-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:30px}
.hobby-card{background:rgba(255,255,255,0.9);backdrop-filter:blur(10px);border-radius:20px;padding:30px;box-shadow:var(--shadow);border:1px solid rgba(255,179,198,0.2);transition:transform .3s}
.hobby-card:hover{transform:translateY(-5px)}
.hobby-card h3{font-family:'ZCOOL KuaiLe',cursive;font-size:1.3rem;color:var(--pink);margin-bottom:15px;display:flex;align-items:center;gap:8px}
.hobby-tag{display:inline-block;padding:6px 16px;margin:4px;border-radius:20px;font-size:.85rem;font-weight:500;transition:transform .2s}
.hobby-tag:hover{transform:scale(1.05)}
.hobby-tag.pink{background:linear-gradient(135deg,var(--tag1-bg),var(--pink-light));color:var(--tag1-color)}
.hobby-tag.purple{background:linear-gradient(135deg,var(--tag2-bg),#e1bee7);color:var(--tag2-color)}
.hobby-tag.gold{background:linear-gradient(135deg,var(--tag3-bg),var(--gold));color:var(--tag3-color)}
.hobby-tag.green{background:linear-gradient(135deg,var(--tag4-bg),#a5d6a7);color:var(--tag4-color)}
.hobby-tag.blue{background:linear-gradient(135deg,var(--tag5-bg),#90caf9);color:var(--tag5-color)}
.shared-badge{display:inline-block;background:var(--shared-grad);color:#fff;padding:3px 12px;border-radius:12px;font-size:.75rem;margin-left:8px;vertical-align:middle}
.together-section{background:linear-gradient(180deg,#f3e5f5 0%,var(--warm) 100%)}
.together-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px}
.together-card{background:rgba(255,255,255,0.9);backdrop-filter:blur(10px);border-radius:20px;padding:25px;text-align:center;box-shadow:var(--shadow);border:1px solid rgba(255,179,198,0.2);transition:transform .3s}
.together-card:hover{transform:translateY(-5px) rotate(1deg)}
.together-icon{font-size:2.5rem;margin-bottom:12px}
.together-card h3{font-family:'ZCOOL KuaiLe',cursive;font-size:1.1rem;color:var(--text);margin-bottom:6px}
.together-card p{font-size:.85rem;color:var(--text-light);line-height:1.5}
.together-count{display:inline-block;background:var(--pink);color:#fff;padding:2px 10px;border-radius:10px;font-size:.75rem;margin-top:8px}
.map-section{background:linear-gradient(180deg,#fce4ec,#fff5f7)}
.map-card{background:rgba(255,255,255,0.9);backdrop-filter:blur(10px);border-radius:20px;padding:25px;box-shadow:var(--shadow);border:1px solid rgba(255,179,198,0.2);margin-bottom:20px}
.map-card h3{font-family:'ZCOOL KuaiLe',cursive;font-size:1.2rem;color:var(--pink);margin-bottom:6px}
.map-card p{font-size:.85rem;color:var(--text-light)}
.map-link{display:inline-block;margin-top:8px;padding:6px 16px;background:var(--pink);color:#fff;border-radius:20px;text-decoration:none;font-size:.85rem;transition:transform .2s}
.map-link:hover{transform:scale(1.05)}
/* 音乐播放器 */
.music-player{position:fixed;bottom:20px;left:20px;z-index:999;background:rgba(255,255,255,0.95);backdrop-filter:blur(10px);border-radius:16px;padding:12px;box-shadow:var(--shadow);display:flex;align-items:center;gap:10px;transition:transform .3s}
.music-player:hover{transform:scale(1.02)}
.music-cover{width:40px;height:40px;border-radius:8px;background:linear-gradient(135deg,#ff6b8a,#c77dba);display:flex;align-items:center;justify-content:center;font-size:20px}
.music-cover img{width:100%;height:100%;border-radius:8px;object-fit:cover}
.music-info{min-width:100px}
.music-title{font-size:.9rem;color:var(--text);font-weight:500}
.music-artist{font-size:.8rem;color:var(--text-light)}
.music-controls{display:flex;align-items:center;gap:8px}
.music-btn{width:32px;height:32px;border-radius:50%;border:none;background:var(--pink-light);color:var(--pink);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;transition:transform .2s}
.music-btn:hover{transform:scale(1.1)}
.music-btn.playing{background:var(--pink);color:#fff}
.music-progress{width:80px;height:4px;background:var(--pink-light);border-radius:2px;overflow:hidden}
.music-close{position:absolute;top:-8px;right:-8px;width:20px;height:20px;border-radius:50%;border:none;background:var(--text-light);color:#fff;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s}.music-player:hover .music-close{opacity:1}.music-progress-bar{height:100%;background:var(--pink);width:0;transition:width .1s}
.footer{text-align:center;padding:40px 20px;background:linear-gradient(135deg,#ffe0e8,#f3e5f5)}
.footer .heart-text{font-family:'ZCOOL KuaiLe',cursive;font-size:1.3rem;color:var(--pink);margin-bottom:8px}
.footer .copyright{font-size:.8rem;color:var(--text-light)}
.lightbox{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:1000;align-items:center;justify-content:center}
.lightbox.active{display:flex}
.lightbox img{max-width:90%;max-height:90%;border-radius:12px}
.lightbox-close{position:absolute;top:20px;right:30px;color:#fff;font-size:2rem;cursor:pointer}
@media(max-width:768px){
.hero h1{font-size:2rem}.avatar{width:75px;height:75px;font-size:30px}.timer-item .num{font-size:1.8rem}.love-timer{padding:20px 25px}
.timeline::before{left:20px}.timeline-item,.timeline-item:nth-child(odd){flex-direction:column;padding-left:50px}
.tl-content,.timeline-item:nth-child(odd) .tl-content,.timeline-item:nth-child(even) .tl-content{margin:0!important;text-align:left!important}
.tl-dot{left:20px}
.gallery-grid{grid-template-columns:repeat(2,1fr)}.gallery-item[style*="grid-row"]{grid-row:span 1!important;aspect-ratio:1!important}
.hobbies-grid{grid-template-columns:1fr}.together-grid{grid-template-columns:repeat(2,1fr)}
}
/* 锁屏样式 */
.lock-screen{position:fixed;top:0;left:0;width:100%;height:100%;background:linear-gradient(135deg,#fff5f7 0%,#ffe0e8 50%,#f5e6ff 100%);z-index:99999;display:flex;align-items:center;justify-content:center;flex-direction:column}
.lock-screen.hidden{display:none}
.lock-box{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border-radius:20px;padding:40px;box-shadow:0 20px 60px rgba(255,107,138,0.2);text-align:center;max-width:400px;width:90%}
.lock-icon{font-size:60px;margin-bottom:20px}
.lock-title{font-family:'ZCOOL KuaiLe',cursive;font-size:1.8rem;color:#ff6b8a;margin-bottom:10px}
.lock-hint{font-size:.9rem;color:#8b6b7d;margin-bottom:25px}
.lock-input{width:100%;padding:14px 20px;font-size:1.1rem;border:2px solid #ffb3c6;border-radius:30px;outline:none;text-align:center;transition:all .3s;letter-spacing:3px}
.lock-input:focus{border-color:#ff6b8a;box-shadow:0 0 0 4px rgba(255,107,138,0.15)}
.lock-input.error{border-color:#e74c3c;animation:shake .5s}
@keyframes shake{0%,100%{transform:translateX(0)}20%,60%{transform:translateX(-8px)}40%,80%{transform:translateX(8px)}}
.lock-btn{width:100%;padding:14px;border:none;border-radius:30px;background:linear-gradient(135deg,#ff6b8a,#c77dba);color:#fff;font-size:1rem;cursor:pointer;margin-top:15px;transition:transform .2s}
.lock-btn:hover{transform:scale(1.02)}
.lock-btn:active{transform:scale(.98)}
.lock-error{color:#e74c3c;font-size:.85rem;margin-top:10px;height:20px}
.lock-attempts{font-size:.75rem;color:#aaa;margin-top:8px}

@media(max-width:480px){.together-grid{grid-template-columns:1fr}.gallery-grid{grid-template-columns:repeat(2,1fr);gap:10px}.section{padding:50px 15px}}
</style>
</head>
<body>
<!-- 锁屏 -->
<div class="lock-screen" id="lockScreen">
  <div class="lock-box">
    <div class="lock-icon">🔐</div>
    <div class="lock-title">页面已加密</div>
    <div class="lock-hint">请输入访问密码</div>
    <input type="password" class="lock-input" id="lockInput" placeholder="输入密码" autocomplete="off">
    <div class="lock-error" id="lockError"></div>
    <button class="lock-btn" onclick="checkPassword()">解锁访问</button>
    <div class="lock-attempts" id="lockAttempts"></div>
  </div>
</div>
<script>
(function(){
  var pw='{$lockHash}';
  var lock='{$pageLock}';
  var key='lk_'+pw.slice(0,8);
  var attempts=0;
  var maxAttempts=5;
  var lockTime=0;
  
  if(lock!=='1'||localStorage.getItem(key)){
    document.getElementById('lockScreen').classList.add('hidden');
    return;
  }
  
  document.body.style.overflow='hidden';
  
  var input=document.getElementById('lockInput');
  input.addEventListener('keypress',function(e){if(e.key==='Enter')checkPassword()});
  input.focus();
  
  window.checkPassword=function(){
    var v=input.value;
    var err=document.getElementById('lockError');
    var att=document.getElementById('lockAttempts');
    
    if(attempts>=maxAttempts){
      var wait=Math.min(Math.pow(2,attempts-maxAttempts)*30,300);
      err.textContent='请等待 '+wait+' 秒后再试';
      return;
    }
    
    // 前端验证（密文比对）
    if(hashPW(v)===pw){
      localStorage.setItem(key,'1');
      document.getElementById('lockScreen').classList.add('hidden');
      document.body.style.overflow='';
    }else{
      attempts++;
      input.classList.add('error');
      input.value='';
      err.textContent='密码错误';
      if(attempts>=3){
        att.textContent='剩余尝试: '+(maxAttempts-attempts)+' 次';
      }
      setTimeout(function(){input.classList.remove('error')},500);
    }
  };
  
  function hashPW(s){
    var h=0x811c9dc5;
    for(var i=0;i<s.length;i++){
      h^=s.charCodeAt(i);
      h=Math.imul(h,0x01000193);
    }
    return (h>>>0).toString(16);
  }
})();
</script>

<div class="theme-switcher">
  <button class="theme-btn pink active" onclick="setTheme('pink')" title="粉色甜美"></button>
  <button class="theme-btn blue" onclick="setTheme('blue')" title="蓝色清新"></button>
</div>
<div class="hearts-container" id="hearts"></div>
<div class="lightbox" id="lightbox" onclick="closeLightbox()"><span class="lightbox-close">&times;</span><img id="lightbox-img" src="" alt=""></div>
<section class="hero">
  <div class="hero-avatar">
    <div class="avatar avatar-left">{$avatarLeftHTML}</div>
    <span class="heart-link">💕</span>
    <div class="avatar avatar-right">{$avatarRightHTML}</div>
  </div>
  <h1>{$this->h($c['title'])}</h1>
  <p class="subtitle">{$this->h($c['subtitle'])}</p>
  <div class="love-timer">
    <div class="label">💕 我们在一起已经</div>
    <div class="date-config"><input type="hidden" id="love-date" value="{$dt}"><input type="date" id="love-date-display" value="{$dateOnly}" style="border:2px solid var(--pink-light);border-radius:10px;padding:8px 16px;font-size:1rem;font-family:inherit;color:var(--text);outline:none;text-align:center;margin-bottom:15px" onchange="document.getElementById('love-date').value=this.value+'T00:00';updateTimer()"></div>
    <div class="timer-grid">
      <div class="timer-item"><div class="num" id="days">0</div><div class="unit">天</div></div>
      <div class="timer-item"><div class="num" id="hours">0</div><div class="unit">小时</div></div>
      <div class="timer-item"><div class="num" id="minutes">0</div><div class="unit">分钟</div></div>
      <div class="timer-item"><div class="num" id="seconds">0</div><div class="unit">秒</div></div>
    </div>
  </div>
  <div class="scroll-hint">↓</div>
</section>
{$sections}
<footer class="footer">
  <div class="heart-text">{$this->h($c['footer_text'])}</div>
  <div class="copyright">Made with ❤️ · 我们的故事</div>
</footer>
<script>
function setTheme(t){document.documentElement.setAttribute('data-theme',t);localStorage.setItem('love_theme',t);document.querySelectorAll('.theme-btn').forEach(b=>b.classList.remove('active'));document.querySelector('.theme-btn.'+t).classList.add('active')}
var st=localStorage.getItem('love_theme')||'pink';setTheme(st);
(function(){var c=document.getElementById('hearts'),h=['💕','💗','💖','❤️','🩷','🤍','🩵','✨'];for(var i=0;i<20;i++){var e=document.createElement('span');e.className='floating-heart';e.textContent=h[Math.floor(Math.random()*h.length)];e.style.left=Math.random()*100+'%';e.style.fontSize=(Math.random()*16+10)+'px';e.style.animationDuration=(Math.random()*10+10)+'s';e.style.animationDelay=(Math.random()*15)+'s';c.appendChild(e)}})();
function getLD(){var s=localStorage.getItem('love_date');return s?new Date(s):new Date(document.getElementById('love-date').value)}
function updateTimer(){var i=document.getElementById('love-date');localStorage.setItem('love_date',i.value);var s=getLD(),n=new Date(),d=n-s;if(d<0)return;document.getElementById('days').textContent=Math.floor(d/864e5);document.getElementById('hours').textContent=String(Math.floor(d%864e5/36e5)).padStart(2,'0');document.getElementById('minutes').textContent=String(Math.floor(d%36e5/6e4)).padStart(2,'0');document.getElementById('seconds').textContent=String(Math.floor(d%6e4/1e3)).padStart(2,'0')}
setInterval(updateTimer,1000);updateTimer();
// 纪念日倒计时实时计算（每分钟刷新，解决静态HTML日期冻结问题）
function updateCountdowns(){
  var now=new Date();
  document.querySelectorAll('.cd-days[data-target]').forEach(function(el){
    var t=new Date(el.getAttribute('data-target')+'T00:00:00');
    if(isNaN(t.getTime()))return;
    var diff=Math.floor((t-now)/864e5);
    // 修正夏令时/时区造成的1天偏差：按本地零点重算
    var t0=new Date(t.getFullYear(),t.getMonth(),t.getDate());
    var n0=new Date(now.getFullYear(),now.getMonth(),now.getDate());
    diff=Math.round((t0-n0)/864e5);
    el.textContent=diff>0?'还有 '+diff+' 天':(diff===0?'就是今天！':'已过 '+Math.abs(diff)+' 天');
  });
}
updateCountdowns();setInterval(updateCountdowns,60000);
function closeLightbox(){document.getElementById('lightbox').classList.remove('active')}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeLightbox()});
</script>
<link rel="stylesheet" href="assets/css/player.css">
<script src="assets/js/player.js"></script>
<script>
var lovePlaylist={$musicJSON};
if(lovePlaylist.length>0){
var lp=new MiniPlayer({playlist:lovePlaylist,autoplay:{$autoplay},hideHours:{$hideHours}});
}
</script>
</body>
</html>
HTML;
    }
}
