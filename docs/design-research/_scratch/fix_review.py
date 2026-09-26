import pathlib

D = pathlib.Path("docs/design-research/prototypes")
files = ["screen-c-event-v1-inline.html", "screen-c-event-v2-preview-column.html", "screen-a-dashboard.html"]

# 1. D-46 says the log is append-last and never re-sorts under the reader.
#    prepend() put the new turn above the older ones: 13, 10, 11, 12.
for fn in files:
    p = D / fn
    s = p.read_text(encoding="utf-8")
    n = s.count("log.prepend(")
    s = s.replace("log.prepend(", "log.appendChild(")
    if n:
        print(f"{fn}: append-last fixed ({n} site)")
    p.write_text(s, encoding="utf-8")

# 2. A turn with no gains rendered ". Energy recovered." - a leading period from
#    joining an empty parts array. Assert on rendered text, not on element presence.
for fn in files:
    p = D / fn
    s = p.read_text(encoding="utf-8")
    a = 'el.innerHTML = `<span class="disc">${e.turn}</span><h4>${e.title}</h4><p>${parts.join(". ")}.${e.extra ? " " + e.extra : ""}</p>`;'
    b = ('const sentence = parts.length ? parts.join(". ") + "." : "";\n'
         '  el.innerHTML = `<span class="disc">${e.turn}</span><h4>${e.title}</h4><p>${sentence}${e.extra ? " " + e.extra : ""}</p>`;')
    if a in s:
        p.write_text(s.replace(a, b, 1), encoding="utf-8")
        print(f"{fn}: leading-period defect fixed")
    a2 = 'el.innerHTML = `<span class="disc">${e.turn}</span><h4>${e.label}<span class="mood'
    if a2 in s:
        c = '<p>${deltas.join(". ")}.${e.note ? " " + e.note : ""}</p>'
        d = '<p>${deltas.length ? deltas.join(". ") + "." : ""}${e.note ? " " + e.note : ""}</p>'
        s2 = s.replace(c, d, 1)
        p.write_text(s2, encoding="utf-8")
        print(f"{fn}: deltas period fixed")

# 3. The step line kept reading "Step 1 of 3" after a commit.
for fn in ["screen-c-event-v1-inline.html", "screen-c-event-v2-preview-column.html"]:
    p = D / fn
    s = p.read_text(encoding="utf-8")
    s = s.replace('  turn += 1;\n  document.querySelector(".chip .n").textContent = String(turn);',
                  '  turn += 1;\n  document.querySelector(".chip .n").textContent = String(turn);\n'
                  '  document.querySelector(".stepline").textContent = "Step 1 of 3 \\u00b7 the event is logged, the next step opens on a real run";', 1)
    p.write_text(s, encoding="utf-8")
    print(f"{fn}: step line updated")

# 4. Variant 2: the preview column landed ABOVE the choice list, so the reader met
#    "Hover a choice to preview it" before the choices existed.
p = D / "screen-c-event-v2-preview-column.html"
s = p.read_text(encoding="utf-8")
s = s.replace(".event>.preview{grid-column:2;grid-row:4;", ".event>.preview{grid-column:2;grid-row:5;", 1)
s = s.replace(""".event>.tag,.event>.title-ribbon,.event>.narr,.event>[role="radiogroup"],.event>.footer,.event>.dots,.event>.stepline{grid-column:1/-1}""",
""".event>.tag,.event>.title-ribbon,.event>.narr,.event>[role="radiogroup"],.event>.footer,.event>.dots,.event>.stepline{grid-column:1}
.event>.tag,.event>.title-ribbon,.event>.narr{grid-column:1/-1}""", 1)
p.write_text(s, encoding="utf-8")
print("variant 2 preview placement fixed")
