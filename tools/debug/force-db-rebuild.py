import os
import stat
import shutil

db_path = r"D:\Projects\umamusume-laravel13\database\database.sqlite"
new_db_path = r"D:\Projects\umamusume-laravel13\database\database.sqlite.new"

print("=== Force file removal attempt ===\n")

# Check what's locking the file using Python's ctypes
import ctypes
from ctypes import wintypes

# Try to unlock the file using Windows API
# The file may be locked by a stale SQLite handle

# First, let's try using shutil.move which can sometimes move locked files on Windows
try:
    # Try to move the locked file
    shutil.move(db_path, db_path + '.old_locked')
    print(f"Moved locked file to: {db_path}.old_locked")
except Exception as e:
    print(f"Cannot move locked file: {e}")
    
    # Try using os rename
    try:
        os.rename(db_path, db_path + '.old_locked')
        print(f"Renamed locked file to: {db_path}.old_locked")
    except Exception as e2:
        print(f"Cannot rename locked file: {e2}")
        
        # Try to create a symlink instead
        try:
            os.symlink(new_db_path, db_path)
            print(f"Created symlink: {db_path} -> {new_db_path}")
        except Exception as e3:
            print(f"Cannot create symlink: {e3}")

# Clean up temp file
if os.path.exists(new_db_path):
    os.remove(new_db_path)
    print(f"Cleaned up: {new_db_path}")