// PHP Dojo — App Logic
// Depends on: SNIPPETS, QUIZZES from data.js

(function () {
  'use strict';

  const TOTAL = 26;
  const TOTAL_Q = 104;
  const PROGRESS_KEY = 'phpdojo_progress';
  const QUIZ_KEY = 'phpdojo_quiz';
  const THEME_KEY = 'phpdojo_theme';
  const DRAWER_W_KEY = 'phpdojo_drawer_w';

  // ── State ──────────────────────────────────────
  let currentTopic = null;
  let progress = loadJSON(PROGRESS_KEY, {});
  let quizScores = loadJSON(QUIZ_KEY, {});

  function loadJSON(key, fallback) {
    try { return JSON.parse(localStorage.getItem(key)) || fallback; }
    catch { return fallback; }
  }
  function saveProgress() { localStorage.setItem(PROGRESS_KEY, JSON.stringify(progress)); }
  function saveQuiz() { localStorage.setItem(QUIZ_KEY, JSON.stringify(quizScores)); }

  // ── DOM refs ───────────────────────────────────
  const $ = (s, p) => (p || document).querySelector(s);
  const $$ = (s, p) => [...(p || document).querySelectorAll(s)];
  const overlay = $('#overlay');
  const drawer = $('#drawer');
  const drawerTitle = $('#drawer-title');
  const drawerBody = $('#drawer-body');
  const codePanel = $('#code-panel');
  const quizPanel = $('#quiz-panel');
  const tabCode = $('#tab-code');
  const tabQuiz = $('#tab-quiz');
  const quizBadge = $('#quiz-badge-tab');
  const searchInput = $('#search');

  // ── Syntax Highlighter ─────────────────────────
  const KW = new Set([
    'echo','print','return','if','else','elseif','foreach','for','while','do',
    'switch','case','break','continue','class','function','interface','trait',
    'abstract','final','extends','implements','new','use','namespace','public',
    'protected','private','static','readonly','const','match','yield','fn',
    'throw','try','catch','finally','declare','enum','null','true','false',
    'string','int','float','bool','array','void','mixed','never','self',
    'parent','as','instanceof','default','require','include'
  ]);

  function esc(s) {
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function highlight(src) {
    let out = '', i = 0;
    while (i < src.length) {
      // Block comment
      if (src[i] === '/' && src[i + 1] === '*') {
        let j = src.indexOf('*/', i + 2);
        if (j === -1) j = src.length; else j += 2;
        out += '<span class="cm">' + esc(src.slice(i, j)) + '</span>';
        i = j; continue;
      }
      // Line comment
      if (src[i] === '/' && src[i + 1] === '/') {
        let j = i; while (j < src.length && src[j] !== '\n') j++;
        out += '<span class="cm">' + esc(src.slice(i, j)) + '</span>';
        i = j; continue;
      }
      // String
      if (src[i] === '"' || src[i] === "'") {
        const q = src[i]; let j = i + 1;
        while (j < src.length) {
          if (src[j] === '\\') { j += 2; continue; }
          if (src[j] === q) { j++; break; }
          j++;
        }
        out += '<span class="str">' + esc(src.slice(i, j)) + '</span>';
        i = j; continue;
      }
      // Variable
      if (src[i] === '$') {
        let j = i + 1;
        while (j < src.length && /\w/.test(src[j])) j++;
        out += '<span class="var">' + esc(src.slice(i, j)) + '</span>';
        i = j; continue;
      }
      // Word
      if (/[a-zA-Z_]/.test(src[i])) {
        let j = i;
        while (j < src.length && /\w/.test(src[j])) j++;
        const w = src.slice(i, j);
        out += KW.has(w) ? '<span class="kw">' + esc(w) + '</span>' : esc(w);
        i = j; continue;
      }
      // Number
      if (/\d/.test(src[i])) {
        let j = i;
        while (j < src.length && /[\d.xXbBoOeE_a-fA-F]/.test(src[j])) j++;
        out += '<span class="num">' + esc(src.slice(i, j)) + '</span>';
        i = j; continue;
      }
      // Other
      out += esc(src[i]); i++;
    }
    return out;
  }

  // ── Drawer ─────────────────────────────────────
  function openDrawer(topicId) {
    const data = SNIPPETS[topicId];
    if (!data) return;
    currentTopic = topicId;

    // Update active node
    $$('.node').forEach(n => n.classList.toggle('active', n.dataset.topic === topicId));

    // Title
    const node = $(`.node[data-topic="${topicId}"]`);
    drawerTitle.textContent = node ? node.textContent.trim() : topicId;

    // Build code panel
    const isRead = !!progress[topicId];
    codePanel.innerHTML =
      '<p class="topic-desc">' + data.desc + '</p>' +
      '<div class="snippet-label">' + esc(data.s1.label) + '</div>' +
      '<pre><code>' + highlight(data.s1.code) + '</code></pre>' +
      '<div class="snippet-label">' + esc(data.s2.label) + '</div>' +
      '<pre><code>' + highlight(data.s2.code) + '</code></pre>' +
      '<button class="mark-read-btn' + (isRead ? ' done' : '') + '" id="mark-read">' +
      (isRead ? '&#10003; Read' : 'Mark as read') + '</button>';

    $('#mark-read').addEventListener('click', function () {
      if (progress[topicId]) {
        delete progress[topicId];
        this.classList.remove('done');
        this.innerHTML = 'Mark as read';
      } else {
        progress[topicId] = Date.now();
        this.classList.add('done');
        this.innerHTML = '&#10003; Read';
      }
      saveProgress();
      updateAllProgress();
    });

    // Build quiz panel
    buildQuiz(topicId);
    updateQuizBadge(topicId);

    // Switch to code tab
    switchTab('code');

    // Open
    drawer.classList.add('open');
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';

    // URL
    history.replaceState(null, '', '#topic=' + topicId);
  }

  function closeDrawer() {
    drawer.classList.remove('open');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    $$('.node').forEach(n => n.classList.remove('active'));
    currentTopic = null;
    history.replaceState(null, '', location.pathname);
  }

  function switchTab(tab) {
    tabCode.classList.toggle('active', tab === 'code');
    tabQuiz.classList.toggle('active', tab === 'quiz');
    codePanel.classList.toggle('active', tab === 'code');
    quizPanel.classList.toggle('active', tab === 'quiz');
    drawerBody.scrollTop = 0;
  }

  // ── Quiz Engine ────────────────────────────────
  function buildQuiz(topicId) {
    const qs = QUIZZES[topicId];
    if (!qs) { quizPanel.innerHTML = '<p class="topic-desc">No quiz for this topic yet.</p>'; return; }

    const scores = quizScores[topicId] || {};
    const answered = Object.keys(scores).length;
    const correct = Object.values(scores).filter(Boolean).length;
    const medal = answered === qs.length ? (correct === qs.length ? '\uD83E\uDD47' : correct >= qs.length * 0.75 ? '\uD83E\uDD48' : correct >= qs.length * 0.5 ? '\uD83E\uDD49' : '') : '';

    let html = '<div class="quiz-header">' +
      '<div class="quiz-score"><b>' + correct + '</b> / ' + qs.length + ' correct ' + medal + '</div>' +
      '<div class="quiz-progress"><div class="quiz-progress-fill" style="width:' + (answered / qs.length * 100) + '%"></div></div>' +
      '</div>';

    qs.forEach(function (q, idx) {
      const wasAnswered = scores[idx] !== undefined;
      const wasCorrect = scores[idx] === true;
      html += '<div class="quiz-q" data-idx="' + idx + '">';
      html += '<div class="quiz-q-num">Q' + (idx + 1) + '</div>';
      html += '<div class="quiz-q-text">' + q.q + '</div>';
      html += '<div class="quiz-opts">';
      q.opts.forEach(function (opt, oi) {
        let cls = 'quiz-opt';
        if (wasAnswered) {
          if (oi === q.answer) cls += ' correct';
          else if (scores[idx + '_sel'] === oi) cls += ' wrong';
        }
        html += '<button class="' + cls + '" data-q="' + idx + '" data-o="' + oi + '"' +
          (wasAnswered ? ' disabled' : '') + '>' + esc(opt) + '</button>';
      });
      html += '</div>';
      html += '<div class="quiz-explanation' + (wasAnswered ? ' show' : '') + '">' +
        (wasAnswered ? (wasCorrect ? '&#10003; ' : '&#10007; ') : '') + esc(q.explanation) + '</div>';
      html += '</div>';
    });

    quizPanel.innerHTML = html;

    // Bind answer clicks
    $$('.quiz-opt:not(:disabled)', quizPanel).forEach(function (btn) {
      btn.addEventListener('click', function () {
        const qi = parseInt(this.dataset.q);
        const oi = parseInt(this.dataset.o);
        answerQuestion(topicId, qi, oi);
      });
    });
  }

  function answerQuestion(topicId, qIdx, selectedOpt) {
    const qs = QUIZZES[topicId];
    const q = qs[qIdx];
    const isCorrect = selectedOpt === q.answer;

    if (!quizScores[topicId]) quizScores[topicId] = {};
    quizScores[topicId][qIdx] = isCorrect;
    quizScores[topicId][qIdx + '_sel'] = selectedOpt;
    saveQuiz();

    // Rebuild the quiz panel
    buildQuiz(topicId);
    updateQuizBadge(topicId);
    updateAllProgress();
  }

  function updateQuizBadge(topicId) {
    const qs = QUIZZES[topicId];
    if (!qs) { quizBadge.textContent = ''; return; }
    const scores = quizScores[topicId] || {};
    const answered = Object.keys(scores).filter(k => !k.includes('_sel')).length;
    quizBadge.textContent = answered + '/' + qs.length;
  }

  // ── Progress ───────────────────────────────────
  function updateAllProgress() {
    const readCount = Object.keys(progress).length;
    $('#read-count').textContent = readCount;
    $('#stat-done').textContent = readCount;
    $('#read-fill').style.width = (readCount / TOTAL * 100) + '%';

    // Quiz totals
    let totalCorrect = 0, totalAnswered = 0;
    Object.keys(quizScores).forEach(function (topicId) {
      const s = quizScores[topicId];
      Object.keys(s).forEach(function (k) {
        if (!k.includes('_sel')) { totalAnswered++; if (s[k]) totalCorrect++; }
      });
    });
    $('#quiz-total-correct').textContent = totalCorrect;
    $('#quiz-total-answered').textContent = totalAnswered;
    $('#quiz-fill').style.width = (totalAnswered > 0 ? totalCorrect / totalAnswered * 100 : 0) + '%';

    // Node read state
    $$('.node').forEach(function (n) {
      n.classList.toggle('read', !!progress[n.dataset.topic]);
    });

    // Section counts
    var sections = { basics: 6, good: 7, pro: 7, geek: 6 };
    Object.keys(sections).forEach(function (sec) {
      var nodes = $$('.node[data-section="' + sec + '"]');
      var done = nodes.filter(function (n) { return !!progress[n.dataset.topic]; }).length;
      var el = $('[data-sec="' + sec + '"]');
      if (el) el.textContent = done + '/' + sections[sec];
      var fill = $('[data-cube="' + sec + '"]');
      if (fill) fill.style.width = (done / sections[sec] * 100) + '%';
    });
  }

  // ── Search ─────────────────────────────────────
  function applySearch(query) {
    const q = query.toLowerCase().trim();
    let any = false;
    $$('.node').forEach(function (n) {
      const name = n.textContent.toLowerCase();
      const tid = n.dataset.topic.replace(/-/g, ' ');
      const data = SNIPPETS[n.dataset.topic];
      const desc = data ? data.desc.replace(/<[^>]+>/g, '').toLowerCase() : '';
      const match = !q || name.includes(q) || tid.includes(q) || desc.includes(q);
      n.hidden = !match;
      if (match) any = true;
    });
    // Hide empty sections
    $$('.section').forEach(function (sec) {
      const visible = $$('.node', sec).some(function (n) { return !n.hidden; });
      sec.hidden = !visible;
    });
    $('#no-results').style.display = any ? 'none' : 'block';
  }

  searchInput.addEventListener('input', function () { applySearch(this.value); });

  // ── Theme ──────────────────────────────────────
  const themeBtn = $('#theme-btn');
  function setTheme(t) {
    document.documentElement.dataset.theme = t;
    themeBtn.innerHTML = t === 'dark' ? '&#9788;' : '&#9789;';
    localStorage.setItem(THEME_KEY, t);
  }
  themeBtn.addEventListener('click', function () {
    setTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
  });

  // ── Scroll progress + back-top ─────────────────
  const scrollBar = $('#scroll-bar');
  const backTop = $('#back-top');

  window.addEventListener('scroll', function () {
    const s = window.scrollY;
    const max = document.documentElement.scrollHeight - window.innerHeight;
    scrollBar.style.width = (max > 0 ? s / max * 100 : 0) + '%';
    backTop.classList.toggle('visible', s > 400);
  }, { passive: true });

  backTop.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // ── Drawer resize ──────────────────────────────
  var resizeHandle = $('#drawer-resize');
  var isResizing = false;

  resizeHandle.addEventListener('mousedown', function (e) {
    isResizing = true;
    e.preventDefault();
  });

  document.addEventListener('mousemove', function (e) {
    if (!isResizing) return;
    var w = window.innerWidth - e.clientX;
    if (w < 300) w = 300;
    if (w > window.innerWidth * 0.9) w = window.innerWidth * 0.9;
    drawer.style.width = w + 'px';
    localStorage.setItem(DRAWER_W_KEY, w);
  });

  document.addEventListener('mouseup', function () { isResizing = false; });

  // Restore drawer width
  var savedW = localStorage.getItem(DRAWER_W_KEY);
  if (savedW) drawer.style.width = savedW + 'px';

  // ── Event bindings ─────────────────────────────
  // Node clicks
  $$('.node').forEach(function (n) {
    n.addEventListener('click', function () { openDrawer(this.dataset.topic); });
  });

  // Drawer close
  overlay.addEventListener('click', closeDrawer);
  $('#drawer-close').addEventListener('click', closeDrawer);

  // Tab switching
  tabCode.addEventListener('click', function () { switchTab('code'); });
  tabQuiz.addEventListener('click', function () { switchTab('quiz'); });

  // Keyboard shortcuts
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeDrawer(); return; }
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    if (e.key === '/' || (e.ctrlKey && e.key === 'k')) {
      e.preventDefault(); searchInput.focus(); return;
    }
    // n/p for next/prev topic when drawer is open
    if (currentTopic && (e.key === 'n' || e.key === 'p')) {
      var nodes = $$('.node:not([hidden])');
      var idx = nodes.findIndex(function (n) { return n.dataset.topic === currentTopic; });
      if (idx === -1) return;
      if (e.key === 'n' && idx < nodes.length - 1) openDrawer(nodes[idx + 1].dataset.topic);
      if (e.key === 'p' && idx > 0) openDrawer(nodes[idx - 1].dataset.topic);
    }
  });

  // ── Deep link ──────────────────────────────────
  function checkHash() {
    var h = location.hash;
    if (h.startsWith('#topic=')) {
      var t = h.replace('#topic=', '');
      if (SNIPPETS[t]) openDrawer(t);
    }
  }
  window.addEventListener('hashchange', checkHash);

  // ── Init ───────────────────────────────────────
  updateAllProgress();
  checkHash();

})();
