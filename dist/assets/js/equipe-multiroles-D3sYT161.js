(function () {
  function hexToRgba(hex, alpha) {
    if (!hex || typeof hex !== 'string' || !hex.startsWith('#')) return hex;
    const h = hex.replace('#', '');
    if (h.length === 3) {
      const r = parseInt(h[0] + h[0], 16);
      const g = parseInt(h[1] + h[1], 16);
      const b = parseInt(h[2] + h[2], 16);
      return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    } else if (h.length === 6) {
      const r = parseInt(h.substring(0, 2), 16);
      const g = parseInt(h.substring(2, 4), 16);
      const b = parseInt(h.substring(4, 6), 16);
      return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }
    return hex;
  }

  function lightenHex(hex, factor) {
    factor = factor || 0.35;
    if (!hex || typeof hex !== 'string' || !hex.startsWith('#')) return hex;
    const h = hex.replace('#', '');
    if (h.length !== 6) return hex;
    let r = parseInt(h.substring(0, 2), 16);
    let g = parseInt(h.substring(2, 4), 16);
    let b = parseInt(h.substring(4, 6), 16);
    r = Math.min(255, Math.round(r + (255 - r) * factor));
    g = Math.min(255, Math.round(g + (255 - g) * factor));
    b = Math.min(255, Math.round(b + (255 - b) * factor));
    return '#' + [r, g, b].map(function (x) { return x.toString(16).padStart(2, '0'); }).join('');
  }

  function darkenHex(hex, factor) {
    factor = factor || 0.65;
    if (!hex || typeof hex !== 'string' || !hex.startsWith('#')) return hex;
    const h = hex.replace('#', '');
    if (h.length !== 6) return hex;
    let r = parseInt(h.substring(0, 2), 16);
    let g = parseInt(h.substring(2, 4), 16);
    let b = parseInt(h.substring(4, 6), 16);
    r = Math.round(r * factor);
    g = Math.round(g * factor);
    b = Math.round(b * factor);
    return '#' + [r, g, b].map(function (x) { return x.toString(16).padStart(2, '0'); }).join('');
  }

  function h(a) {
    if (!a || typeof a !== 'string') return '';
    let e = a.replace(/^[\s»›>«‹▶►▸•·|―\-–—]+|[\s»›>«‹▶►▸•·|―\-–—]+$/gu, '').trim().replace(/_/g, ' ').replace(/\s+/g, ' ');
    return (
      e === e.toUpperCase() &&
        e.length > 2 &&
        (e = e.toLowerCase().replace(/(?:^|\s|-|\/)\S/g, function (n) {
          return n.toUpperCase();
        })),
      e
    );
  }

  function A(a, e, n) {
    const s = (a || '').toLowerCase().trim(),
      t = [],
      i = new Set();
    function c(r) {
      if (!r || typeof r !== 'string') return;
      let l = r.trim();
      [
        'ÉQUIPE TECHNIQUE',
        'EQUIPE TECHNIQUE',
        'DÉVELOPPEUR',
        'DEVELOPPEUR',
        'DEVELOPER',
        'CO-FONDATEUR',
        'FONDATEUR',
        'CO-CEO',
        'CEO',
        'CO-CRÉATEUR',
        'CO-CREATEUR',
        'CRÉATEUR',
        'CREATEUR',
        'GÉRANT STAFF',
        'GERANT STAFF',
        'GÉRANT',
        'GERANT',
        'MANAGER',
        'RESPONSABLE',
        'ADMINISTRATEUR',
        'ADMIN',
        'MODÉRATEUR',
        'MODERATEUR',
        'SUPPORT',
        'RECRUTEUR',
        'STAFF',
      ].forEach(function (o) {
        const E = new RegExp('([a-zA-ZÀ-ÿ0-9])(' + o + ')', 'gi');
        l = l.replace(E, '$1 | $2');
      });
      l.split(/[\r\n\/|,•·–—]+/).forEach(function (o) {
        o.split('|').forEach(function (u) {
          const f = h(u);
          if (!f || f.length <= 1) return;
          const m = f.toLowerCase();
          m === s || s.includes(m) || i.has(m) || (i.add(m), t.push(f));
        });
      });
    }
    return e && c(e), Array.isArray(n) && n.forEach(function (r) { c(r); }), t;
  }

  function g() {
    document.querySelectorAll('.sd-team-card--hierarchy').forEach(function (e) {
      if (e.dataset.tiltInit === 'true') return;
      e.dataset.tiltInit = 'true';
      let n = null,
        s = 0,
        t = 0;
      function i() {
        e.style.transform = 'perspective(1000px) rotateX(' + s.toFixed(2) + 'deg) rotateY(' + t.toFixed(2) + 'deg) translateY(-6px)';
        n = null;
      }
      e.addEventListener('pointermove', function (c) {
        const r = e.getBoundingClientRect(),
          l = c.clientX - r.left,
          d = c.clientY - r.top,
          p = r.width / 2,
          o = r.height / 2;
        s = ((d - o) / o) * -4.5;
        t = ((l - p) / p) * 4.5;
        n || (n = requestAnimationFrame(i));
      });
      e.addEventListener('pointerdown', function () {
        e.style.transform = 'perspective(1000px) scale(0.985) translateY(-2px)';
      });
      e.addEventListener('pointerup', function () {
        e.style.transform = 'perspective(1000px) rotateX(' + s.toFixed(2) + 'deg) rotateY(' + t.toFixed(2) + 'deg) translateY(-6px)';
      });
      e.addEventListener('pointerleave', function () {
        n && cancelAnimationFrame(n);
        s = 0;
        t = 0;
        e.style.transform = '';
      });
    });
  }

  async function R() {
    try {
      const a = await (window.SDAuth ? SDAuth.sdApi('/team/list.php') : fetch('/api/team/list.php').then(function (t) { return t.json(); }));
      const e = a && a.members ? a.members : [];
      if (!Array.isArray(e) || e.length === 0) return;
      const roleColors = a.role_colors || {};
      if (!roleColors['createur'] || roleColors['createur'].toLowerCase() === '#ffffff') {
        roleColors['createur'] = '#eeae59';
      }
      document.querySelectorAll('.sd-team-card').forEach(function(card) {
        if (card.textContent && card.textContent.toLowerCase().includes('south district bot')) {
          card.remove();
        }
      });
      const n = new Map();
      e.forEach(function (t) {
        if (!t.pseudo || t.pseudo.toLowerCase().includes('south district bot')) return;
        const i = t.pseudo.toLowerCase().trim();
        const c = A(t.staff_label, t.staff_title, t.secondary_labels);
        let sCol = t.staff_color || roleColors[t.staff_role] || null;
        if (t.staff_role === 'createur' && (!sCol || sCol.toLowerCase() === '#ffffff')) {
          sCol = '#eeae59';
        }
        n.set(i, {
          id: t.id,
          pseudo: t.pseudo,
          staff_role: t.staff_role,
          staff_label: t.staff_label,
          staff_color: sCol,
          secondary_roles: t.secondary_roles || [],
          secondary_labels: t.secondary_labels || [],
          secondary_colors: t.secondary_colors || {},
          subRoles: c,
        });
      });
      if (n.size === 0) return;

      const applyEnhancements = function () {
        // 1. Tiers et en-têtes
        document.querySelectorAll('.sd-team-tier').forEach(function (tierEl) {
          const roleKey = tierEl.dataset.tier || tierEl.dataset.role;
          let tierColor = roleColors[roleKey];
          if (roleKey === 'createur' && (!tierColor || tierColor.toLowerCase() === '#ffffff')) {
            tierColor = '#eeae59';
          }
          if (tierColor && tierColor !== '#000000') {
            const dot = tierEl.querySelector('.sd-team-tier__dot');
            if (dot) {
              dot.style.background = tierColor;
              dot.style.boxShadow = '0 0 14px ' + tierColor;
            }
            const bar = tierEl.querySelector('.sd-team-tier__bar');
            if (bar) {
              bar.style.backgroundColor = tierColor;
              bar.style.boxShadow = '0 0 14px ' + tierColor;
            }
            const h2 = tierEl.querySelector('.sd-team-tier__head h2, h2');
            if (h2) {
              h2.style.borderColor = hexToRgba(tierColor, 0.3);
              h2.style.boxShadow = '0 10px 30px ' + hexToRgba(tierColor, 0.2);
            }
          }
        });

        // 2. Cartes membres
        document.querySelectorAll('.sd-team-card').forEach(function (card) {
          const nameEl = card.querySelector('.sd-team-card__name');
          if (!nameEl) return;
          const r = nameEl.textContent.toLowerCase().trim();
          let l = n.get(r);
          if (!l) {
            // Recherche par correspondance partielle si unicode
            for (const item of n.values()) {
              if (item.pseudo && (item.pseudo.includes(nameEl.textContent.trim()) || nameEl.textContent.trim().includes(item.pseudo))) {
                l = item;
                break;
              }
            }
          }
          if (!l) return;

          const col = l.staff_color;
          if (col && col !== '#000000') {
            const dark = darkenHex(col, 0.65);
            const light = lightenHex(col, 0.65);
            const glow = hexToRgba(col, 0.55);

            card.style.setProperty('--tier-accent', col);
            card.style.setProperty('--role-accent', col);
            card.style.setProperty('--role-dark', dark);
            card.style.setProperty('--role-light', light);
            card.style.setProperty('--tier-glow', glow);
            card.style.borderColor = hexToRgba(col, 0.4);

            // Appliquer sur les spans de dégradé du pseudo
            nameEl.style.setProperty('color', col, 'important');
            nameEl.querySelectorAll('.sd-role-gradient-text, span').forEach(function (span) {
              span.style.setProperty('--role-dark', dark, 'important');
              span.style.setProperty('--role-light', light, 'important');
              span.style.setProperty('--role-glow', glow, 'important');
              span.style.setProperty('background-image', 'linear-gradient(90deg, ' + dark + ' 0%, ' + dark + ' 20%, ' + light + ' 50%, ' + dark + ' 80%, ' + dark + ' 100%)', 'important');
              span.style.setProperty('-webkit-background-clip', 'text', 'important');
              span.style.setProperty('background-clip', 'text', 'important');
              span.style.setProperty('-webkit-text-fill-color', 'transparent', 'important');
              span.style.setProperty('color', 'transparent', 'important');
              span.style.setProperty('text-shadow', '0 0 14px ' + glow, 'important');
            });

            const badge = card.querySelector('.sd-team-card__badge--role');
            if (badge) {
              badge.style.color = col;
              badge.style.borderColor = hexToRgba(col, 0.45);
              badge.style.backgroundColor = hexToRgba(col, 0.18);
            }

            const roleTitle = card.querySelector('.sd-team-card__role-title');
            if (roleTitle) {
              roleTitle.style.color = col;
              roleTitle.style.borderImage = 'linear-gradient(90deg, ' + col + ' 40%, transparent) 1';
            }
          }

          const d = card.querySelector('.sd-team-card__caption');
          if (!d || d.dataset.proEnhanced === 'true') return;
          d.dataset.proEnhanced = 'true';
          d.querySelectorAll('.sd-team-card__role-tag, .sd-team-card__secondary-badges, .sd-team-capsules').forEach(function (o) { o.remove(); });

          if (l.subRoles.length > 0) {
            const o = document.createElement('div');
            o.className = 'sd-team-subroles';
            l.subRoles.forEach(function (E) {
              const u = document.createElement('span');
              u.className = 'sd-team-subrole-tag';
              u.textContent = E;

              // Recherche couleur du sous-rôle
              let tagColor = null;
              if (l.secondary_labels && l.secondary_colors) {
                const idx = l.secondary_labels.findIndex(function (sl) { return sl && sl.toLowerCase() === E.toLowerCase(); });
                if (idx !== -1 && l.secondary_roles[idx]) {
                  tagColor = l.secondary_colors[l.secondary_roles[idx]];
                }
              }
              if (!tagColor) {
                for (const rKey in roleColors) {
                  if (rKey.toLowerCase() === E.toLowerCase().replace(/\s+/g, '_')) {
                    tagColor = roleColors[rKey];
                    break;
                  }
                }
              }
              if (tagColor && tagColor !== '#000000') {
                u.style.color = tagColor;
                u.style.borderColor = hexToRgba(tagColor, 0.35);
                u.style.backgroundColor = hexToRgba(tagColor, 0.12);
              }

              o.appendChild(u);
            });
            d.appendChild(o);
          }
        });
        g();
      };

      // Exécuter immédiatement et avec un observateur de mutations DOM
      applyEnhancements();
      const container = document.getElementById('sd-team-hierarchy');
      if (container) {
        const obs = new MutationObserver(function () {
          applyEnhancements();
        });
        obs.observe(container, { childList: true, subtree: true });
      }
      setTimeout(applyEnhancements, 250);
      setTimeout(applyEnhancements, 800);
      setTimeout(applyEnhancements, 1800);
    } catch (err) {}
  }

  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', R) : R();
})();
