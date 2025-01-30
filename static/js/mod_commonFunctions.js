const commonFunctions = {
  // 半角英字を組み合わせたランダムな文字列を生成する
  generatedChars() {
    let chars = "";
    const base = ["a","i","u","e","o","b","c","d","f","g","h","k","l","m","n","p","r","s","t","y","z"];
    const shi_in = ["a","i","u","e","o"];
    for(let i=0;i<3;i++){
      let base_rand = Math.floor(Math.random() * base.length);
      let shiIn_rand = Math.floor(Math.random() * 5);
      chars += base[base_rand];
      chars += shi_in[shiIn_rand];
    }

    return chars;
  },

  // 半角数字を組み合わせたランダムな数値を生成する
  generatedQuatNums() {
    let nums = "";
    for(let i=0;i<3;i++){
      let num = String(Math.floor(Math.random() * 10));
      nums += num;
    }

    return nums;
  },

  // 操作日の週の月曜日と次の週の月曜日をyyyy-mm-DD形式で取得
  getMondayDates() {
    const today = new Date();
    const currentMonday = new Date(today);
    currentMonday.setDate(today.getDate() - (today.getDay() - 1 + 7) % 7);
    
    const nextMonday = new Date(currentMonday);
    nextMonday.setDate(currentMonday.getDate() + 7);
    
    const formatDate = (date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    };
    
    return [formatDate(currentMonday), formatDate(nextMonday)];
  },

  // 引数に指定した日（yyyy-MM-dd形式）を起点に、その日を含めた7日分の日付を取得
  getWeekDates(startDate) {
    const dates = [];
    const currentDate = new Date(startDate);

    for (let i = 0; i < 7; i++) {
      const year = currentDate.getFullYear();
      const month = String(currentDate.getMonth() + 1).padStart(2, '0');
      const day = String(currentDate.getDate()).padStart(2, '0');
      dates.push(`${year}-${month}-${day}`);
      currentDate.setDate(currentDate.getDate() + 1);
    }

    return dates;
  },

  // yyyy-MM-ddをyyyy年M月d日に変換
  convertDateFormat(dateString) {
    const [year, month, day] = dateString.split('-');
    return `${year}年${parseInt(month)}月${parseInt(day)}日`;
  },

  // 特殊文字列を全角に変換＆改行コードを/nに統一
  processString(inputString) {
    const charMap = {
      '[': '［',
      '<': '＜',
      '>': '＞',
      '&': '＆',
      '?': '？',
      '$': '＄',
      '@': '＠',
      '!': '！'
    };

    let processedString = inputString
      .replace(/[<>&?$@!\[\]]/g, match => charMap[match] || match)
      .replace(/\r\n/g, '\n');

    return processedString;
  }
}

export default commonFunctions;