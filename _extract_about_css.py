import json
import sys

path = r"C:\Users\kate\.cursor\projects\c-Users-kate-Desktop-diplom\agent-transcripts\5672e543-52a1-4e1c-ad32-6722ccb367ef\5672e543-52a1-4e1c-ad32-6722ccb367ef.jsonl"

def main():
    with open(path, encoding="utf-8") as f:
        lines = f.readlines()
    for lineno in range(558, min(565, len(lines)) + 1):
        line = lines[lineno - 1]
        if "about-page.css" not in line:
            continue
        o = json.loads(line)
        msg = o.get("message") or {}
        content = msg.get("content") or []
        for block in content:
            if not isinstance(block, dict):
                continue
            t = block.get("type")
            if t == "text":
                text = block.get("text", "")
                if "about-page.css" in text and len(text) > 500:
                    print("TEXT line", lineno, "len", len(text))
            if t == "tool_result":
                tr = block.get("tool_result") or block
                # different schemas
                for k in block:
                    if k == "content" and isinstance(block[k], list):
                        for sub in block[k]:
                            if isinstance(sub, dict) and sub.get("type") == "text":
                                tx = sub.get("text", "")
                                if "fullscreen-about" in tx:
                                    out = r"c:\Users\kate\Desktop\diplom\_old_about_page.css"
                                    open(out, "w", encoding="utf-8").write(tx)
                                    print("wrote", out, len(tx))
                                    return 0
    # dump structure of line 560
    o = json.loads(lines[559])
    print(json.dumps(o, ensure_ascii=False)[:2000])
    return 1

if __name__ == "__main__":
    sys.exit(main())
