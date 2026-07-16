import os
import sys
sys.path.insert(0, os.path.abspath(os.path.dirname(__file__)))

from flask import Flask, request, jsonify
from flask_cors import CORS
import traceback

# Import the faster peglit version directly
try:
    import peglit_min
except ImportError:
    print("Error: peglit_min module not found. Make sure this script is run from the correct directory.")
    sys.exit(1)

app = Flask(__name__)
CORS(app)  # Enable CORS for the local API

@app.route('/api/compute_linker', methods=['POST'])
def compute_linker():
    try:
        data = request.get_json()
        if not data:
            return jsonify({'error': 'No JSON data provided'}), 400

        spacer = data.get('spacer')
        scaffold = data.get('scaffold')
        template = data.get('template')
        pbs = data.get('pbs')
        motif = data.get('motif', "CGCGGTTCTATCTAGTTACGCGTTAAACCAACTAGAA")
        linker_length = data.get('linker_length', 8)

        if not all([spacer, scaffold, template, pbs]):
            return jsonify({'error': 'Missing required sequence parameters (spacer, scaffold, template, pbs)'}), 400

        linker_pattern = "N" * linker_length

        # By default, pegLIT does num_repeats=10 and num_steps=250 (2500 RNA folds)
        # This takes ~4 minutes in Python.
        linkers = peglit_min.pegLIT(
            seq_spacer=spacer,
            seq_scaffold=scaffold,
            seq_template=template,
            seq_pbs=pbs,
            seq_motif=motif,
            linker_pattern=linker_pattern,
            num_repeats=10, 
            num_steps=200
        )

        result = "NNNNNNNN"
        if linkers and len(linkers) > 0:
            result = linkers[0].upper()

        return jsonify({'linker': result, 'success': True})

    except Exception as e:
        print("Error during pegLIT computation:")
        traceback.print_exc()
        return jsonify({'error': str(e), 'linker': 'NNNNNNNN', 'success': False}), 500

if __name__ == '__main__':
    print("Starting pegLIT Linker Server on port 5001...")
    # Run on port 5001 to avoid common 5000 conflicts
    app.run(host='127.0.0.1', port=5001, debug=False)
