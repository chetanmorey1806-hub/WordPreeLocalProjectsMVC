#!/usr/bin/env python3
"""
Draws the GMS magazine covers.

Reads the job list written by covers.php and renders one 1080x1440 cover per
issue: a full-bleed photograph under the GMS masthead, the issue's lead
stories as cover lines, and the issue title across the foot.

    python3 cover-art.py jobs.json
"""
import json
import os
import sys

from PIL import Image, ImageChops, ImageDraw, ImageEnhance, ImageFilter, ImageFont

HERE = os.path.dirname(os.path.abspath(__file__))
FONTS = os.path.join(HERE, 'fonts')

W, H = 1080, 1440
PAD = 72
WHITE = (255, 255, 255, 255)
SOFT = (255, 255, 255, 222)
GOLD = (227, 200, 117, 255)   # --gold-light
NAVY = (7, 17, 29)            # --navy-dark

# Words that stay upright in the title; the rest set in gold italic.
PLAIN = {'the', 'a', 'an', 'of', 'and', 'issue'}


def serif(size, weight=700, italic=False):
    name = 'PlayfairDisplay-Italic.ttf' if italic else 'PlayfairDisplay.ttf'
    font = ImageFont.truetype(os.path.join(FONTS, name), size)
    font.set_variation_by_axes([weight])
    return font


def sans(size, weight='SemiBold'):
    return ImageFont.truetype(os.path.join(FONTS, 'Poppins-%s.ttf' % weight), size)


def tracked_len(text, font, track):
    return sum(font.getlength(c) for c in text) + track * max(0, len(text) - 1)


def tracked(draw, x, y, text, font, fill, track, right=False):
    """Letter-spaced text on a baseline; PIL has no tracking of its own."""
    if right:
        x -= tracked_len(text, font, track)
    for ch in text:
        draw.text((x, y), ch, font=font, fill=fill, anchor='ls')
        x += font.getlength(ch) + track


def wrap(text, font, width, most):
    lines, cur = [], ''
    for word in text.split():
        trial = (cur + ' ' + word).strip()
        if cur and font.getlength(trial) > width:
            lines.append(cur)
            cur = word
        else:
            cur = trial
    if cur:
        lines.append(cur)
    if len(lines) > most:
        lines = lines[:most]
        last = lines[-1]
        while ' ' in last and font.getlength(last + '…') > width:
            last = last.rsplit(' ', 1)[0]
        lines[-1] = last.rstrip(' ,.;:—-') + '…'
    return lines


def photo(path, focus):
    """Crop to the cover's 3:4, centred on `focus` across the width."""
    im = Image.open(path).convert('RGB')
    w, h = im.size
    cw = min(w, round(h * W / H))
    ch = min(h, round(cw * H / W))
    x = round((w - cw) * focus)
    y = (h - ch) // 2
    im = im.crop((x, y, x + cw, y + ch)).resize((W, H), Image.LANCZOS)
    im = ImageEnhance.Contrast(im).enhance(1.06)
    return ImageEnhance.Color(im).enhance(0.9)


def ramp(stops, vertical=True):
    """A full-cover greyscale mask whose strength follows `stops` [(pos, alpha)]."""
    n = H if vertical else W
    vals = []
    for i in range(n):
        t = i / (n - 1)
        a = stops[-1][1] if t >= stops[-1][0] else stops[0][1]
        for (p0, a0), (p1, a1) in zip(stops, stops[1:]):
            if p0 <= t <= p1:
                a = a0 + (a1 - a0) * (t - p0) / (p1 - p0)
                break
        vals.append(round(255 * a))
    strip = Image.new('L', (1, n) if vertical else (n, 1))
    strip.putdata(vals)
    return strip.resize((W, H))


def shade(im, mask):
    """Lay navy over the photo through `mask`."""
    return Image.composite(Image.new('RGB', (W, H), NAVY), im, mask)


def title_lines(title, width):
    """Mixed upright/italic title, as large as fits on two lines."""
    for size in (116, 108, 100, 92, 84, 76):
        upright, italic = serif(size, 800), serif(size, 700, italic=True)
        lines, cur, cur_w = [], [], 0
        for word in title.split():
            plain = word.lower() in PLAIN
            font, fill = (upright, WHITE) if plain else (italic, GOLD)
            ww = font.getlength(word)
            gap = font.getlength(' ') if cur else 0
            if cur and cur_w + gap + ww > width:
                lines.append(cur)
                cur, cur_w, gap = [], 0, 0
            cur.append((word, font, fill))
            cur_w += gap + ww
        if cur:
            lines.append(cur)
        if len(lines) <= 2:
            break
    return lines, size


def cover(job):
    im = photo(job['photo'], job.get('focus', 0.5))
    # Navy at the head for the masthead, down the left for the cover lines,
    # and deepest at the foot for the title.
    im = shade(im, ramp([(0, .9), (.16, .62), (.34, 0)]))
    im = shade(im, ramp([(0, .5), (.52, 0)], vertical=False))
    # Extra depth behind the cover lines only, so bright photos stay readable
    # without darkening the whole picture.
    im = shade(im, ImageChops.multiply(
        ramp([(0, .74), (.34, .6), (.64, 0)], vertical=False),
        ramp([(.17, 0), (.25, 1), (.54, 1), (.66, 0)])))
    im = shade(im, ramp([(.44, 0), (.66, .66), (1, .96)]))

    ink = Image.new('RGBA', (W, H), (0, 0, 0, 0))
    d = ImageDraw.Draw(ink)

    # Top right: brand, month, edition, right-aligned to the masthead baseline.
    base = 252
    tracked(d, W - PAD, base - 84, job['brand'].upper(), sans(24), GOLD, 4.5, right=True)
    tracked(d, W - PAD, base - 42, job['month'].upper(), sans(27), WHITE, 3.5, right=True)
    tracked(d, W - PAD, base, job['edition'].upper(), sans(20, 'Medium'), SOFT, 3.5, right=True)
    block = max(tracked_len(job['brand'].upper(), sans(24), 4.5),
                tracked_len(job['month'].upper(), sans(27), 3.5))

    # Masthead, as large as the space left of that block allows.
    room = W - 2 * PAD - block - 44
    size = 236
    while size > 120 and serif(size, 900).getlength(job['masthead']) > room:
        size -= 4
    d.text((PAD - 6, base), job['masthead'], font=serif(size, 900), fill=WHITE, anchor='ls')
    d.rectangle((PAD, base + 30, W - PAD, base + 32), fill=GOLD)

    # Cover lines down the left.
    y = base + 116
    col = 480
    for k, line in enumerate(job['lines'][:2]):
        d.rectangle((PAD, y - 34, PAD + 44, y - 31), fill=GOLD)
        tracked(d, PAD, y, line['kicker'].upper(), sans(21), GOLD, 3.2)
        y += 16
        font = serif(46 if k == 0 else 37, 700)
        lh = round(font.size * 1.18)
        for text in wrap(line['text'], font, col, 3):
            y += lh
            d.text((PAD, y), text, font=font, fill=WHITE, anchor='ls')
        y += 92

    # Foot: excerpt at the bottom, the title above it, a gold rule above that.
    ex_font = sans(27, 'Medium')
    ex = wrap(job['excerpt'], ex_font, W - 2 * PAD, 2)
    ex_lh = 40
    y = H - PAD - ex_lh * (len(ex) - 1)
    for i, text in enumerate(ex):
        d.text((PAD, y + i * ex_lh), text, font=ex_font, fill=SOFT, anchor='ls')

    lines, tsize = title_lines(job['title'], W - 2 * PAD)
    t_lh = round(tsize * 1.04)
    # Last title baseline sits clear of the excerpt's first line and the
    # title's own descenders.
    first = y - 88 - t_lh * (len(lines) - 1)
    for i, runs in enumerate(lines):
        x = PAD - 3
        for word, font, fill in runs:
            d.text((x, first + i * t_lh), word, font=font, fill=fill, anchor='ls')
            x += font.getlength(word + ' ')
    rule = first - round(tsize * .78) - 34
    d.rectangle((PAD, rule, PAD + 96, rule + 5), fill=GOLD)

    # A soft shadow under all the type keeps it off the busiest photographs.
    shadow = ink.split()[3].filter(ImageFilter.GaussianBlur(9)).point(lambda v: int(v * .8))
    im = Image.composite(Image.new('RGB', (W, H), NAVY), im, shadow)
    im.paste(ink, (0, 0), ink)

    im.save(job['out'], 'JPEG', quality=88, optimize=True, progressive=True)


def main():
    with open(sys.argv[1], encoding='utf-8') as fh:
        jobs = json.load(fh)
    for job in jobs:
        cover(job)
        print('drew', os.path.basename(job['out']))


if __name__ == '__main__':
    main()
