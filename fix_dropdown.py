import os

dirs_to_check = ['pages/user', 'pages/admincashier']
files_changed = 0

for d in dirs_to_check:
    for root, _, files in os.walk(d):
        for f in files:
            if f.endswith('.html'):
                filepath = os.path.join(root, f)
                with open(filepath, 'r', encoding='utf-8') as file:
                    content = file.read()

                # Find </header>
                header_end_idx = content.find('</header>')
                if header_end_idx == -1:
                    continue
                
                # Find the dropdown menu
                dropdown_start_str = '<div class="dropdown-menu" id="dropdown-menu">'
                dropdown_idx = content.find(dropdown_start_str)
                if dropdown_idx == -1:
                    continue
                    
                # Ensure dropdown is AFTER header (it might already be inside)
                if dropdown_idx < header_end_idx:
                    continue
                
                # Find matching </div>
                div_count = 0
                dropdown_end_idx = -1
                
                i = dropdown_idx
                while i < len(content):
                    if content[i:i+4] == '<div':
                        div_count += 1
                        i += 4
                    elif content[i:i+6] == '</div>':
                        div_count -= 1
                        if div_count == 0:
                            dropdown_end_idx = i + 6
                            break
                        i += 6
                    else:
                        i += 1
                        
                if dropdown_end_idx != -1:
                    dropdown_block = content[dropdown_idx:dropdown_end_idx]
                    
                    # Remove the dropdown block from its original position
                    new_content = content[:dropdown_idx] + content[dropdown_end_idx:]
                    
                    # Insert it before </header>
                    new_header_end_idx = new_content.find('</header>')
                    final_content = new_content[:new_header_end_idx] + '  ' + dropdown_block + '\n' + new_content[new_header_end_idx:]
                    
                    with open(filepath, 'w', encoding='utf-8') as file:
                        file.write(final_content)
                    print(f'Fixed {filepath}')
                    files_changed += 1

print(f'Total files fixed: {files_changed}')
