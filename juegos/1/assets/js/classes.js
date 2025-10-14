export class Enemy
{
    imageUrl;
    posY;

    constructor()
    {
        this.posY = Math.floor((Math.random() * (window.innerHeight - 50)) + 25);
    };    

    /**
     * @param { URL } imageUrl 
     * @returns 
     */
    setImage(imageUrl)
    {
        this.imageUrl = imageUrl;
        return this;
    };

    /**
     * @param { number } yPixels 
     * @returns 
     */
    setCustomPosY(yPixels)
    {
        this.posY = yPixels;
        return this;
    };

};