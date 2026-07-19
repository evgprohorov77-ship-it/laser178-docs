import os
import sys

# Add repository root to PYTHONPATH so tests can import Advisor.*
ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", ".."))
if ROOT not in sys.path:
    sys.path.insert(0, ROOT)
