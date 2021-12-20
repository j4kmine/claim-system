export default { 
    slugify: str => str != null ? str.toLowerCase().split(" ").join("_") : '',
    unslugify: str => str != null ? str.split("_").join(" ") : ''
}