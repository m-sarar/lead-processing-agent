/**
 * Phonetic transcription utilities
 */
export function getPhoneticTranscription(name: string): string {
  if (!name || name.trim() === '') {
    return '';
  }

  const text = name.trim().toLowerCase();
  const vowels = ['a', 'e', 'i', 'o', 'u'];
  const consonantSounds: Record<string, string> = {
    // Common consonant sound mappings
    'b': 'b',
    'c': 'k',
    'd': 'd',
    'f': 'f',
    'g': 'g',
    'h': 'h',
    'j': 'j',
    'k': 'k',
    'l': 'l',
    'm': 'm',
    'n': 'n',
    'p': 'p',
    'q': 'k',
    'r': 'r',
    's': 's',
    't': 't',
    'v': 'v',
    'w': 'w',
    'x': 'ks',
    'y': 'i',
    'z': 'z',

    // Common digraphs
    'ch': 'ch',
    'sh': 'sh',
    'th': 'θ',
    'ph': 'f',
    'gh': 'g',
    'kn': 'n',
    'wh': 'w',
    'qu': 'kw',
  };

  // Simple phonetic transcription algorithm
  let result = '';
  let i = 0;
  const len = text.length;

  while (i < len) {
    let found = false;

    // Check for digraphs first
    for (const [digraph, sound] of Object.entries(consonantSounds)) {
      if (digraph.length === 2 && text.substr(i, 2) === digraph) {
        result += sound;
        i += 2;
        found = true;
        break;
      }
    }

    if (found) continue;

    // Check for single characters
    const char = text[i];
    if (consonantSounds[char]) {
      result += consonantSounds[char];
      i += 1;
      found = true;
    }

    if (found) continue;

    // Vowels - keep as is
    if (vowels.includes(char)) {
      result += char;
      i += 1;
      found = true;
    }

    if (found) continue;

    // Default: just add the character
    result += char;
    i += 1;
  }

  return result;
}