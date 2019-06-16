const express = require("express");
var bb = require("express-busboy");
const md5 = require("md5");
const path = require("path");
const rimraf = require("rimraf");

class Api {
  constructor() {
    this.store = new StoreKeyKeyAndKeyLink();
    const app = express();
    bb.extend(app, {
      upload: true,
      path: "./store/",
      allowedPath: /./
    });
    this.createApiShareFolder(app);
    this.createApiPointMediaDevice(app);
    this.createApiPointLinkByKey(app);
    this.createApiPointPostFile(app);
    this.createApiPointRecordStart(app);
    this.createApiPointRecordStop(app);
    this.createApiPointGetInfo(app);
    this.createApiPointGetRecords(app);
    app.listen(8087);
  }
  createApiShareFolder(app) {
    app.use("/store", express.static("store"));
  }
  createApiPointMediaDevice(app) {
    app.get("/getusermedia.bundle.js", (req, res) => {
      res.sendFile(path.join(__dirname, "./public", "getusermedia.bundle.js"));
    });
    app.get("/*", (req, res, next) => {
      if (!this.store.getByLink(req.url)) {
        res.send(
          "Страница не найдена | Не правильный url адрес | Устройство не подключено"
        );
        return;
      }
      next();
    });
    app.get("/*", (req, res) => {
      res.sendFile(path.join(__dirname, "./public", "index.html"));
    });
  }
  createApiPointLinkByKey(app) {
    app.post("/LinkByKey", (req, res) => {
      res.send(this.linkByKey(req.body));
    });
  }
  linkByKey({ key }) {
    if (!key) {
      return {
        error: "key not found"
      };
    }
    let link = `/${md5(key).slice(0, 5)}`;
    this.store.set(key, link, {});
    return {
      error: false,
      link: "http://localhost:8087" + link
    };
  }
  createApiPointPostFile(app) {
    app.post("/postFile", (req, res) => {
      res.send(this.postFile(req));
    });
  }
  postFile(req) {
    var record = false;
    var store;
    if (req.query.pathname) {
      store = this.store.getByLink(req.query.pathname);
      record = store.record;
      if (!store.files) {
        store.files = [];
      }
      store.files.push({
        file: req.files.audio_data,
        time: JSON.parse(req.body.info)
      });
    }
    return {
      record
    };
  }
  createApiPointGetInfo(app) {
    app.post("/getInfo", (req, res) => {
      res.send(this.getInfo(req));
    });
  }
  getInfo(req) {
    var connection = false;
    var record = false;
    if (req.query.pathname && this.store.getByLink(req.query.pathname)) {
      connection = true;
    }
    if (connection && this.store.getByLink(req.query.pathname).record) {
      record = true;
    }
    return {
      connection,
      record
    };
  }
  createApiPointRecordStart(app) {
    app.post("/recordStart", (req, res) => {
      res.send(this.recordStart(req.body));
    });
  }
  recordStart({ key }) {
    let error = false;
    if (!key) {
      error = true;
    }
    if (this.store.getByKey(key) && !error) {
      this.store.getByKey(key).record = true;
    } else {
      error = true;
    }
    return {
      error
    };
  }
  createApiPointRecordStop(app) {
    app.post("/recordStop", (req, res) => {
      res.send(this.recordStop(req.body));
    });
  }
  recordStop({ key }) {
    let error = false;
    if (!key) {
      error = true;
    }
    if (this.store.getByKey(key) && !error) {
      this.store.getByKey(key).record = false;
    } else {
      error = true;
    }
    return {
      error
    };
  }

  createApiPointGetRecords(app) {
    app.post("/getRecords", (req, res) => {
      res.send(this.getRecords(req.body));
    });
  }
  getRecords({ key }) {
    let error = false;
    let files = [];

    if (!key) {
      error = true;
    }

    let store = this.store.getByKey(key);
    if (!error && store && store.files) {
      files = JSON.parse(JSON.stringify(store.files));
      files.forEach(({ file }) => {
        file.url = `http://localhost:8087/store/${file.uuid}/${file.field}/${
          file.filename
        }`;
      });
    } else {
      error = true;
    }

    return {
      error,
      files
    };
  }
}

class StoreKeyKeyAndKeyLink {
  constructor() {
    this.mapKeyToStore = {};
    this.mapLinkToKey = {};
    this.mapKeyToLink = {};

    setInterval(() => {
      this.checkGarbage();
    }, 300000);
  }
  getByKey(key) {
    if (typeof this.mapKeyToStore[key] === "undefined") {
      return false;
    }
    this.setLastConnectTime(key);
    return this.mapKeyToStore[key];
  }
  getByLink(link) {
    if (typeof this.mapLinkToKey[link] === "undefined") {
      return false;
    }
    this.setLastConnectTime(this.mapLinkToKey[link]);
    return this.mapKeyToStore[this.mapLinkToKey[link]];
  }
  set(key, link, store) {
    this.mapKeyToStore[key] = store;
    this.mapLinkToKey[link] = key;
    this.mapKeyToLink[key] = link;
    this.setLastConnectTime(key);
  }
  deleteByKey(key) {
    delete this.mapKeyToStore[key];
    delete this.mapLinkToKey[this.mapKeyToLink[key]];
    delete this.mapKeyToLink[key];
  }
  deleteByLink(link) {
    delete this.mapKeyToStore[this.mapLinkToKey[link]];
    delete this.mapKeyToLink[this.mapLinkToKey[link]];
    delete this.mapLinkToKey[link];
  }
  setLastConnectTime(key) {
    if (this.mapKeyToStore[key]) {
      this.mapKeyToStore[key].lastConnectTime = Date.now();
    }
  }
  checkGarbage() {
    let currentTime = Date.now();
    for (let key in this.mapKeyToStore) {
      if (currentTime - this.mapKeyToStore[key].lastConnectTime > 300000) {
        this.rmGarbageFiles(this.mapKeyToStore[key]);
        this.deleteByKey(key);
      }
    }
  }
  rmGarbageFiles(store) {
    if (!store.files) {
      return;
    }
    store.files.forEach(file => {
      rimraf(`./store/${file.file.uuid}`, () => {});
    });
  }
}

new Api();
