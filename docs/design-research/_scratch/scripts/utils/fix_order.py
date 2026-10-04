import pathlib
import re

p = pathlib.Path("docs/design-research/DESIGN.md")
s = p.read_text(encoding="utf-8")

# Pull the two component sections out of the end of section 8.
m = re.search(r"### 6\.19 Fan readout[\s\S]*?(?=### 8\.4 Race selection, F5)", s)
assert m, "component block not found"
components = m.group(0).rstrip() + "\n"
s = s.replace(components, "", 1)

# Renumber the duplicate 8.4 to 8.9 and keep it at the end of section 8.
s = s.replace("### 8.4 Race selection, F5", "### 8.9 Race selection, F5", 1)

# Re-insert the components after 6.18, before section 7.
anchor = "\n---\n\n## 7. Motif and ornament budget"
assert anchor in s
s = s.replace(anchor, "\n" + components + anchor, 1)

p.write_text(s, encoding="utf-8")

order = re.findall(r"^#{2,3} (?:6\.1[5-9]|6\.2[0-9]|7\.|8\.[0-9]|9\.)[^\n]*", s, flags=re.M)
print("\n".join(order))
